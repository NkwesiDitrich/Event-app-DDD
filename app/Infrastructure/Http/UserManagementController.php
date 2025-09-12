<?php

namespace App\Infrastructure\Http;

use App\Http\Controllers\Controller;
use App\Application\Commands\Registration\CheckInParticipantCommand;
use App\Application\Commands\Registration\UnattendParticipantCommand;
use App\Application\Queries\Registration\GetUserRegistrationsQuery;
use App\Application\Handlers\Registration\CheckInParticipantHandler;
use App\Application\Handlers\Registration\UnattendParticipantHandler;
use App\Application\Handlers\Registration\GetUserRegistrationsHandler;
use App\Infrastructure\Persistence\EloquentEventRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class UserManagementController extends Controller
{
    public function __construct(
        private CheckInParticipantHandler $checkInHandler,
        private UnattendParticipantHandler $unattendHandler,
        private GetUserRegistrationsHandler $getUserRegistrationsHandler,
        private EloquentEventRepository $eventRepository
    ) {}

    /**
     * Display the User Management page
     */
    public function UserManagementPage(Request $request): View
    {
        try {
            $userId = auth()->id();
            $eventId = $request->get('event_id');

            // Get registrations for this user's events
            $query = new GetUserRegistrationsQuery($userId, $eventId);
            $registrations = $this->getUserRegistrationsHandler->handle($query);

            // Get user's events for the filter dropdown
            $events = $this->getViewCompatibleEvents($userId);

            return view('backend.pages.dashboard.user-management-page', compact('registrations', 'events', 'eventId'));
        } catch (\Exception $e) {
            Log::error('User Management page error: ' . $e->getMessage());
            
            // Provide fallback data
            $registrations = [];
            $events = collect([]);
            $eventId = null;
            
            return view('backend.pages.dashboard.user-management-page', compact('registrations', 'events', 'eventId'))
                ->with('error', 'Error loading user management data');
        }
    }

    /**
     * Get list of registrations for API
     */
    public function RegistrationList(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $eventId = $request->get('event_id');

            $query = new GetUserRegistrationsQuery($userId, $eventId);
            $registrations = $this->getUserRegistrationsHandler->handle($query);

            // Convert to array format expected by frontend
            $data = array_map(function ($registration) {
                return [
                    'id' => $registration->getId(),
                    'name' => $registration->getName()->getValue(),
                    'mobile' => $registration->getMobile()->getValue(),
                    'email' => $registration->getEmail() ? $registration->getEmail()->getValue() : '',
                    'remark' => $registration->getRemark() ? $registration->getRemark()->getValue() : '',
                    'date' => $registration->getFormattedDate(),
                    'event_title' => $registration->getEventTitle() ?? 'Unknown Event',
                    'participant_user_name' => $registration->getParticipantUserName() ?? 'Unknown User',
                    'checked_in' => $registration->isCheckedIn(),
                    'checked_in_at' => $registration->getCheckedInAt() ? $registration->getCheckedInAt()->format('Y-m-d H:i:s') : null,
                    'check_in_status' => $registration->getCheckInStatus(),
                    'created_at' => $registration->getFormattedCreatedAt()
                ];
            }, $registrations);

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Registration list error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load registrations'
            ], 500);
        }
    }

    /**
     * Check in a participant
     */
    public function CheckInParticipant(Request $request): JsonResponse
    {
        try {
            $registrationId = $request->input('registration_id');
            $checkIn = $request->input('check_in', true);
            $userId = auth()->id();

            if (!$registrationId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration ID is required'
                ], 400);
            }

            $command = new CheckInParticipantCommand($registrationId, $userId, $checkIn);
            $success = $this->checkInHandler->handle($command);

            if ($success) {
                $action = $checkIn ? 'checked in' : 'checked out';
                return response()->json([
                    'status' => 'success',
                    'message' => "Participant successfully {$action}"
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to update check-in status. You may not have permission for this event.'
                ], 403);
            }
        } catch (\Exception $e) {
            Log::error('Check-in participant error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating check-in status'
            ], 500);
        }
    }

    /**
     * Unattend a participant (remove registration)
     */
    public function UnattendParticipant(Request $request): JsonResponse
    {
        try {
            $registrationId = $request->input('registration_id');
            $userId = auth()->id();

            if (!$registrationId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration ID is required'
                ], 400);
            }

            $command = new UnattendParticipantCommand($registrationId, $userId);
            $success = $this->unattendHandler->handle($command);

            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Participant registration successfully removed'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to remove registration. You may not have permission for this event.'
                ], 403);
            }
        } catch (\Exception $e) {
            Log::error('Unattend participant error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while removing registration'
            ], 500);
        }
    }

    /**
     * Get events in format compatible with view expectations
     */
    private function getViewCompatibleEvents($userId)
    {
        try {
            // Use direct database query for maximum performance and view compatibility
            $events = \Illuminate\Support\Facades\DB::table('events')
                ->join('categories', 'events.categorie_id', '=', 'categories.id')
                ->select(
                    'events.id',
                    'events.title',
                    'events.description',
                    'events.date',
                    'events.time',
                    'events.location',
                    'events.type',
                    'events.image',
                    'events.user_id',
                    'events.categorie_id',
                    'events.created_at',
                    'events.updated_at',
                    'categories.name as category_name'
                )
                ->where('events.user_id', $userId)
                ->orderBy('events.date', 'desc')
                ->get();

            // Convert to simple objects that the view can access directly
            return $events->map(function ($event) {
                return (object) [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'date' => $event->date,
                    'time' => $event->time,
                    'location' => $event->location,
                    'type' => $event->type,
                    'image' => $event->image,
                    'user_id' => $event->user_id,
                    'categorie_id' => $event->categorie_id,
                    'category_name' => $event->category_name,
                    'created_at' => $event->created_at,
                    'updated_at' => $event->updated_at
                ];
            });

        } catch (\Exception $e) {
            Log::error('Get view compatible events failed: ' . $e->getMessage());
            return collect([]);
        }
    }
}
