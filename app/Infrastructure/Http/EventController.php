<?php

namespace App\Infrastructure\Http;

use App\Application\Commands\CreateEventCommand;
use App\Application\Commands\UpdateEventCommand;
use App\Application\Commands\DeleteEventCommand;
use App\Application\Queries\GetEventQuery;
use App\Application\Queries\GetUserEventsQuery;
use App\Application\Handlers\CreateEventHandler;
use App\Application\Handlers\UpdateEventHandler;
use App\Application\Handlers\DeleteEventHandler;
use App\Application\Handlers\GetEventHandler;
use App\Application\Handlers\GetUserEventsHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use InvalidArgumentException;

class EventController extends Controller
{
    private CreateEventHandler $createEventHandler;
    private UpdateEventHandler $updateEventHandler;
    private DeleteEventHandler $deleteEventHandler;
    private GetEventHandler $getEventHandler;
    private GetUserEventsHandler $getUserEventsHandler;

    public function __construct(
        CreateEventHandler $createEventHandler,
        UpdateEventHandler $updateEventHandler,
        DeleteEventHandler $deleteEventHandler,
        GetEventHandler $getEventHandler,
        GetUserEventsHandler $getUserEventsHandler
    ) {
        $this->createEventHandler = $createEventHandler;
        $this->updateEventHandler = $updateEventHandler;
        $this->deleteEventHandler = $deleteEventHandler;
        $this->getEventHandler = $getEventHandler;
        $this->getUserEventsHandler = $getUserEventsHandler;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id ?? 1; // Get from authenticated user
            $type = $request->get('type');
            $page = (int) $request->get('page', 1);
            $perPage = (int) $request->get('per_page', 10);

            $query = new GetUserEventsQuery($userId, $type, $page, $perPage);
            $events = $this->getUserEventsHandler->handle($query);

            return response()->json([
                'success' => true,
                'data' => array_map(fn($event) => $this->eventToArray($event), $events),
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => count($events)
                ]
            ]);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $query = new GetEventQuery($id);
            $event = $this->getEventHandler->handle($query);

            return response()->json([
                'success' => true,
                'data' => $this->eventToArray($event)
            ]);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id ?? 1; // Get from authenticated user

            $command = new CreateEventCommand(
                $request->input('title'),
                $request->input('description'),
                $request->input('date'),
                $request->input('time', ''),
                $request->input('location'),
                $request->input('type', 'Recent'),
                $userId,
                (int) $request->input('categorie_id'),
                $request->input('image')
            );

            $event = $this->createEventHandler->handle($command);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => $this->eventToArray($event)
            ], 201);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $userId = $request->user()->id ?? 1; // Get from authenticated user

            $command = new UpdateEventCommand(
                $id,
                $request->input('title'),
                $request->input('description'),
                $request->input('date'),
                $request->input('time', ''),
                $request->input('location'),
                $request->input('type', 'Recent'),
                $userId,
                $request->input('image')
            );

            $event = $this->updateEventHandler->handle($command);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'data' => $this->eventToArray($event)
            ]);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $userId = $request->user()->id ?? 1; // Get from authenticated user

            $command = new DeleteEventCommand($id, $userId);
            $deleted = $this->deleteEventHandler->handle($command);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete event'
            ], 500);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    // Additional DDD-specific endpoints
    public function upcoming(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id ?? 1;
            $query = new GetUserEventsQuery($userId, null, 1, 50);
            $events = $this->getUserEventsHandler->handle($query);

            // Filter upcoming events using domain logic
            $upcomingEvents = array_filter($events, fn($event) => $event->isUpcoming());

            return response()->json([
                'success' => true,
                'data' => array_map(fn($event) => $this->eventToArray($event), $upcomingEvents)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function featured(): JsonResponse
    {
        try {
            // This would typically use a different query handler for public events
            $userId = 1; // For demo purposes
            $query = new GetUserEventsQuery($userId, 'Feature', 1, 10);
            $events = $this->getUserEventsHandler->handle($query);

            return response()->json([
                'success' => true,
                'data' => array_map(fn($event) => $this->eventToArray($event), $events)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function today(): JsonResponse
    {
        try {
            $userId = 1; // For demo purposes
            $query = new GetUserEventsQuery($userId, null, 1, 50);
            $events = $this->getUserEventsHandler->handle($query);

            // Filter today's events using domain logic
            $todaysEvents = array_filter($events, fn($event) => $event->isToday());

            return response()->json([
                'success' => true,
                'data' => array_map(fn($event) => $this->eventToArray($event), $todaysEvents)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    private function eventToArray($event): array
    {
        return [
            'id' => $event->getId(),
            'title' => $event->getTitle()->getValue(),
            'description' => $event->getDescription()->getValue(),
            'short_description' => $event->getShortDescription(),
            'date' => $event->getDate()->getFormattedDate(),
            'human_date' => $event->getDate()->getHumanReadableDate(),
            'time' => $event->getTime()->getValue(),
            'time_12h' => $event->getTime()->get12HourFormat(),
            'time_of_day' => $event->getTimeOfDay(),
            'location' => $event->getLocation()->getValue(),
            'type' => $event->getType()->getValue(),
            'image' => $event->getImage(),
            'user_id' => $event->getUserId(),
            'category_id' => $event->getCategoryId(),
            'created_at' => $event->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $event->getUpdatedAt()->format('Y-m-d H:i:s'),
            // Rich domain information
            'is_upcoming' => $event->isUpcoming(),
            'is_past' => $event->isPast(),
            'is_featured' => $event->isFeatured(),
            'is_today' => $event->isToday(),
            'is_tomorrow' => $event->isTomorrow(),
            'is_this_week' => $event->isThisWeek(),
            'days_until_event' => $event->getDaysUntilEvent(),
            'is_online' => $event->isOnlineEvent(),
            'is_physical' => $event->isPhysicalEvent(),
            'has_image' => $event->hasImage(),
            'is_business_hours' => $event->isBusinessHours(),
            'can_be_modified' => $event->canBeModified(),
            'can_be_deleted' => $event->canBeDeleted(),
            'event_summary' => $event->getEventSummary(),
        ];
    }
}
