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

// NEW: Import the enhanced User Management handlers
use App\Application\Handlers\UserManagement\GetEventParticipantsHandler;
use App\Application\Handlers\UserManagement\GetEventsCreatedHandler;
use App\Application\Queries\UserManagement\GetEventParticipantsQuery;
use App\Application\Queries\UserManagement\GetEventsCreatedQuery;

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
        private EloquentEventRepository $eventRepository,
        // NEW: Add the enhanced handlers
        private GetEventParticipantsHandler $getEventParticipantsHandler,
        private GetEventsCreatedHandler $getEventsCreatedHandler
    ) {}

    // ========================================
    // EXISTING METHODS (Keep as they are)
    // ========================================

    /**
     * Display the User Management page (EXISTING - Keep this)
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
     * Get list of registrations for API (EXISTING - Keep this)
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

    // ========================================
    // NEW ENHANCED DROPDOWN METHODS
    // ========================================

    /**
     * NEW: User Management Dashboard (Main dropdown page)
     */
    public function UserManagementDashboard(): View
    {
        return view('backend.pages.dashboard.user-management-dashboard');
    }

    /**
     * NEW: Event Participants Page (Dropdown option 1)
     */
    public function EventParticipantsPage(): View
    {
        return view('backend.pages.dashboard.event-participants-page');
    }

    /**
     * NEW: Events Created Page (Dropdown option 2)
     */
    public function EventsCreatedPage(): View
    {
        return view('backend.pages.dashboard.events-created-page');
    }

    /**
     * NEW: Get Event Participants (AJAX endpoint)
     */
    public function GetEventParticipants(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $eventId = $request->get('event_id');
            $status = $request->get('status');
            $page = (int) $request->get('page', 1);
            $perPage = (int) $request->get('per_page', 10);

            $query = new GetEventParticipantsQuery($userId, $eventId, $status, $page, $perPage);
            $result = $this->getEventParticipantsHandler->handle($query);

            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Get event participants error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load event participants'
            ], 500);
        }
    }

    /**
     * NEW: Get Events Created (AJAX endpoint)
     */
    public function GetEventsCreated(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $status = $request->get('status');
            $page = (int) $request->get('page', 1);
            $perPage = (int) $request->get('per_page', 12);

            $query = new GetEventsCreatedQuery($userId, $status, $page, $perPage);
            $result = $this->getEventsCreatedHandler->handle($query);

            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Get events created error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load events created'
            ], 500);
        }
    }

    /**
     * NEW: Get Event Participants by Event ID (for modal)
     */
    public function GetEventParticipantsByEventId(Request $request, $eventId): JsonResponse
    {
        try {
            $userId = auth()->id();
            
            $query = new GetEventParticipantsQuery($userId, $eventId, null, 1, 100);
            $result = $this->getEventParticipantsHandler->handle($query);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'participants' => $result['participants'] ?? []
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get event participants by ID error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load event participants'
            ], 500);
        }
    }

    // ========================================
    // EXISTING METHODS (Keep as they are)
    // ========================================

    /**
     * Check in a participant (EXISTING - Keep this, but enhance it)
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
     * Unattend a participant (EXISTING - Keep this)
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
     * Get events in format compatible with view expectations (EXISTING - Keep this)
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
