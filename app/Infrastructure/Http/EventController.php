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
use Illuminate\Support\Facades\DB;
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

    /**
     * Display the event management page
     * This method was missing and causing the BadMethodCallException
     */
    public function EventPage()
    {
        return view('backend.pages.dashboard.event-page');
    }

    /**
     * API method to create a new event
     */
    public function EventCreate(Request $request): JsonResponse
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
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API method to list events - Fixed to return data in format expected by frontend
     */
    public function EventList(Request $request): JsonResponse
    {
        try {
            // Use direct database query to get events with categories for faster loading
            $userId = $request->user()->id ?? 1;
            
            $events = DB::table('events')
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
                ->orderBy('events.created_at', 'desc')
                ->get();

            // Transform data to match frontend expectations
            $transformedEvents = $events->map(function ($event) {
                return [
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
                    'created_at' => $event->created_at,
                    'updated_at' => $event->updated_at,
                    'category' => [
                        'name' => $event->category_name
                    ]
                ];
            });

            // Return direct array as expected by frontend
            return response()->json($transformedEvents);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API method to update an event
     */
    public function EventUpdate(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id ?? 1; // Get from authenticated user
            $eventId = (int) $request->input('id');

            $command = new UpdateEventCommand(
                $eventId,
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
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API method to delete an event
     */
    public function EventDelete(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id ?? 1; // Get from authenticated user
            $eventId = (int) $request->input('id');

            $command = new DeleteEventCommand($eventId, $userId);
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
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API method to get event by ID
     */
    public function EventByID(Request $request): JsonResponse
    {
        try {
            $eventId = (int) $request->input('id');
            
            // Use direct database query for faster response
            $event = DB::table('events')
                ->join('categories', 'events.categorie_id', '=', 'categories.id')
                ->select(
                    'events.*',
                    'categories.name as category_name'
                )
                ->where('events.id', $eventId)
                ->first();

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'error' => 'Event not found'
                ], 404);
            }

            $transformedEvent = [
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
                'created_at' => $event->created_at,
                'updated_at' => $event->updated_at,
                'category' => [
                    'name' => $event->category_name
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $transformedEvent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    // Original DDD methods (keeping for API compatibility)
    public function index(Request $request): JsonResponse
    {
        return $this->EventList($request);
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
        return $this->EventCreate($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        // Set the ID in the request for consistency with EventUpdate method
        $request->merge(['id' => $id]);
        return $this->EventUpdate($request);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        // Set the ID in the request for consistency with EventDelete method
        $request->merge(['id' => $id]);
        return $this->EventDelete($request);
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
