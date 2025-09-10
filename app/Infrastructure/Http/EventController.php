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
use Illuminate\Support\Facades\Storage;
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
            $userId = $request->user()->id ?? 1;

            // Handle file upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('events', 'public');
            }

            // Direct database insert for better performance
            $eventId = DB::table('events')->insertGetId([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'date' => $request->input('date'),
                'time' => $request->input('time', ''),
                'location' => $request->input('location'),
                'type' => $request->input('type', 'Recent'),
                'user_id' => $userId,
                'categorie_id' => (int) $request->input('categorie_id'),
                'image' => $imagePath,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(1); // Frontend expects simple 1 for success

        } catch (\Exception $e) {
            return response()->json(0); // Frontend expects 0 for failure
        }
    }

    /**
     * API method to list events - Optimized for fast loading
     */
    public function EventList(Request $request): JsonResponse
    {
        try {
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

            return response()->json($transformedEvents);

        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * API method to update an event - Optimized for fast response
     */
    public function EventUpdate(Request $request): JsonResponse
    {
        try {
            $eventId = (int) $request->input('id');
            $userId = $request->user()->id ?? 1;

            // Get current event data
            $currentEvent = DB::table('events')->where('id', $eventId)->where('user_id', $userId)->first();
            
            if (!$currentEvent) {
                return response()->json(0);
            }

            $updateData = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'date' => $request->input('date'),
                'time' => $request->input('time', ''),
                'location' => $request->input('location'),
                'type' => $request->input('type', 'Recent'),
                'categorie_id' => (int) $request->input('categorie_id'),
                'updated_at' => now()
            ];

            // Handle file upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($currentEvent->image && Storage::disk('public')->exists($currentEvent->image)) {
                    Storage::disk('public')->delete($currentEvent->image);
                }
                
                $updateData['image'] = $request->file('image')->store('events', 'public');
            }

            // Direct database update for better performance
            $updated = DB::table('events')
                ->where('id', $eventId)
                ->where('user_id', $userId)
                ->update($updateData);

            return response()->json($updated ? 1 : 0); // Frontend expects 1 for success, 0 for failure

        } catch (\Exception $e) {
            return response()->json(0);
        }
    }

    /**
     * API method to delete an event - Optimized for fast response
     */
    public function EventDelete(Request $request): JsonResponse
    {
        try {
            $eventId = (int) $request->input('id');
            $userId = $request->user()->id ?? 1;
            $oldImage = $request->input('oldImage');

            // Delete the image file if exists
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }

            // Direct database delete for better performance
            $deleted = DB::table('events')
                ->where('id', $eventId)
                ->where('user_id', $userId)
                ->delete();

            return response()->json($deleted ? 1 : 0); // Frontend expects 1 for success, 0 for failure

        } catch (\Exception $e) {
            return response()->json(0);
        }
    }

    /**
     * API method to get event by ID - Optimized for fast response
     */
    public function EventByID(Request $request): JsonResponse
    {
        try {
            $eventId = (int) $request->input('id');
            
            // Direct database query for fastest response
            $event = DB::table('events')
                ->where('id', $eventId)
                ->first();

            if (!$event) {
                return response()->json([]);
            }

            // Return data in format expected by frontend (direct access to fields)
            return response()->json([
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
                'updated_at' => $event->updated_at
            ]);

        } catch (\Exception $e) {
            return response()->json([]);
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
        $request->merge(['id' => $id]);
        return $this->EventUpdate($request);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
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
            $userId = 1;
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
            $userId = 1;
            $query = new GetUserEventsQuery($userId, null, 1, 50);
            $events = $this->getUserEventsHandler->handle($query);

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
