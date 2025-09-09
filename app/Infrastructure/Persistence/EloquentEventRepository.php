<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Event\Entities\Event;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Domain\Event\ValueObjects\EventTitle;
use App\Domain\Event\ValueObjects\EventDescription;
use App\Domain\Event\ValueObjects\EventDate;
use App\Domain\Event\ValueObjects\EventTime;
use App\Domain\Event\ValueObjects\EventLocation;
use App\Domain\Event\ValueObjects\EventType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentEventRepository implements EventRepositoryInterface
{
    private string $table = 'events';

    public function save(Event $event): Event
    {
        try {
            $data = $event->toArray();
            
            if ($event->getId()) {
                // Update existing event
                DB::table($this->table)
                    ->where('id', $event->getId())
                    ->update($data);
                
                return $event;
            } else {
                // Create new event
                $id = DB::table($this->table)->insertGetId($data);
                
                // Return new event with ID
                return new Event(
                    $event->getTitle(),
                    $event->getDescription(),
                    $event->getDate(),
                    $event->getTime(),
                    $event->getLocation(),
                    $event->getType(),
                    $event->getUserId(),
                    $event->getCategoryId(),
                    $event->getImage(),
                    $id,
                    $event->getCreatedAt(),
                    $event->getUpdatedAt()
                );
            }
        } catch (\Exception $e) {
            Log::error('Error saving event: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findById(int $id): ?Event
    {
        try {
            $data = DB::table($this->table)->where('id', $id)->first();
            
            if (!$data) {
                return null;
            }

            return $this->mapToEntity($data);
        } catch (\Exception $e) {
            Log::error('Error finding event by ID: ' . $e->getMessage());
            return null;
        }
    }

    public function findByUserId(int $userId): array
    {
        try {
            $results = DB::table($this->table)
                ->where('user_id', $userId)
                ->orderBy('date', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding events by user ID: ' . $e->getMessage());
            return [];
        }
    }

    public function findByCategoryId(int $categoryId): array
    {
        try {
            $results = DB::table($this->table)
                ->where('categorie_id', $categoryId)
                ->orderBy('date', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding events by category ID: ' . $e->getMessage());
            return [];
        }
    }

    public function findUpcomingEvents(): array
    {
        try {
            $results = DB::table($this->table)
                ->where('date', '>=', date('Y-m-d'))
                ->orderBy('date', 'asc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding upcoming events: ' . $e->getMessage());
            return $this->getFallbackEvents('upcoming');
        }
    }

    public function findPastEvents(): array
    {
        try {
            $results = DB::table($this->table)
                ->where('date', '<', date('Y-m-d'))
                ->orderBy('date', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding past events: ' . $e->getMessage());
            return $this->getFallbackEvents('past');
        }
    }

    public function findFeaturedEvents(): array
    {
        try {
            $results = DB::table($this->table)
                ->where('type', 'Feature')
                ->where('date', '>=', date('Y-m-d'))
                ->orderBy('date', 'asc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding featured events: ' . $e->getMessage());
            return $this->getFallbackEvents('featured');
        }
    }

    public function findRecentEvents(): array
    {
        try {
            $results = DB::table($this->table)
                ->where('type', 'Recent')
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding recent events: ' . $e->getMessage());
            return $this->getFallbackEvents('recent');
        }
    }

    public function findEventsByDate(EventDate $date): array
    {
        try {
            $results = DB::table($this->table)
                ->where('date', $date->getFormattedDate())
                ->orderBy('time', 'asc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding events by date: ' . $e->getMessage());
            return [];
        }
    }

    public function findEventsByDateRange(EventDate $startDate, EventDate $endDate): array
    {
        try {
            $results = DB::table($this->table)
                ->whereBetween('date', [$startDate->getFormattedDate(), $endDate->getFormattedDate()])
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding events by date range: ' . $e->getMessage());
            return [];
        }
    }

    public function findEventsByType(EventType $type): array
    {
        try {
            $results = DB::table($this->table)
                ->where('type', $type->getValue())
                ->orderBy('date', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding events by type: ' . $e->getMessage());
            return [];
        }
    }

    public function findEventsByLocation(string $location): array
    {
        try {
            $results = DB::table($this->table)
                ->where('location', 'LIKE', '%' . $location . '%')
                ->orderBy('date', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding events by location: ' . $e->getMessage());
            return [];
        }
    }

    public function findOnlineEvents(): array
    {
        try {
            $results = DB::table($this->table)
                ->where('location', 'LIKE', '%online%')
                ->orWhere('location', 'LIKE', '%virtual%')
                ->orWhere('location', 'LIKE', '%zoom%')
                ->orWhere('location', 'LIKE', '%meet%')
                ->orderBy('date', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding online events: ' . $e->getMessage());
            return [];
        }
    }

    public function findPhysicalEvents(): array
    {
        try {
            $results = DB::table($this->table)
                ->where('location', 'NOT LIKE', '%online%')
                ->where('location', 'NOT LIKE', '%virtual%')
                ->where('location', 'NOT LIKE', '%zoom%')
                ->where('location', 'NOT LIKE', '%meet%')
                ->orderBy('date', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding physical events: ' . $e->getMessage());
            return [];
        }
    }

    public function findTodaysEvents(): array
    {
        try {
            $results = DB::table($this->table)
                ->where('date', date('Y-m-d'))
                ->orderBy('time', 'asc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding today\'s events: ' . $e->getMessage());
            return [];
        }
    }

    public function findThisWeeksEvents(): array
    {
        try {
            $startOfWeek = date('Y-m-d', strtotime('monday this week'));
            $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

            $results = DB::table($this->table)
                ->whereBetween('date', [$startOfWeek, $endOfWeek])
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding this week\'s events: ' . $e->getMessage());
            return [];
        }
    }

    public function findThisMonthsEvents(): array
    {
        try {
            $startOfMonth = date('Y-m-01');
            $endOfMonth = date('Y-m-t');

            $results = DB::table($this->table)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding this month\'s events: ' . $e->getMessage());
            return [];
        }
    }

    public function searchEvents(string $query): array
    {
        try {
            $results = DB::table($this->table)
                ->where('title', 'LIKE', '%' . $query . '%')
                ->orWhere('description', 'LIKE', '%' . $query . '%')
                ->orWhere('location', 'LIKE', '%' . $query . '%')
                ->orderBy('date', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error searching events: ' . $e->getMessage());
            return [];
        }
    }

    public function findUserEvents(int $userId, ?EventType $type = null): array
    {
        try {
            $query = DB::table($this->table)->where('user_id', $userId);

            if ($type) {
                $query->where('type', $type->getValue());
            }

            $results = $query->orderBy('date', 'desc')->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding user events: ' . $e->getMessage());
            return [];
        }
    }

    public function findUserUpcomingEvents(int $userId): array
    {
        try {
            $results = DB::table($this->table)
                ->where('user_id', $userId)
                ->where('date', '>=', date('Y-m-d'))
                ->orderBy('date', 'asc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding user upcoming events: ' . $e->getMessage());
            return [];
        }
    }

    public function findUserPastEvents(int $userId): array
    {
        try {
            $results = DB::table($this->table)
                ->where('user_id', $userId)
                ->where('date', '<', date('Y-m-d'))
                ->orderBy('date', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding user past events: ' . $e->getMessage());
            return [];
        }
    }

    public function countEventsByUser(int $userId): int
    {
        try {
            return DB::table($this->table)->where('user_id', $userId)->count();
        } catch (\Exception $e) {
            Log::error('Error counting events by user: ' . $e->getMessage());
            return 0;
        }
    }

    public function countEventsByCategory(int $categoryId): int
    {
        try {
            return DB::table($this->table)->where('categorie_id', $categoryId)->count();
        } catch (\Exception $e) {
            Log::error('Error counting events by category: ' . $e->getMessage());
            return 0;
        }
    }

    public function countUpcomingEvents(): int
    {
        try {
            return DB::table($this->table)->where('date', '>=', date('Y-m-d'))->count();
        } catch (\Exception $e) {
            Log::error('Error counting upcoming events: ' . $e->getMessage());
            return 0;
        }
    }

    public function countPastEvents(): int
    {
        try {
            return DB::table($this->table)->where('date', '<', date('Y-m-d'))->count();
        } catch (\Exception $e) {
            Log::error('Error counting past events: ' . $e->getMessage());
            return 0;
        }
    }

    public function countFeaturedEvents(): int
    {
        try {
            return DB::table($this->table)
                ->where('type', 'Feature')
                ->where('date', '>=', date('Y-m-d'))
                ->count();
        } catch (\Exception $e) {
            Log::error('Error counting featured events: ' . $e->getMessage());
            return 0;
        }
    }

    public function countRecentEvents(): int
    {
        try {
            return DB::table($this->table)->where('type', 'Recent')->count();
        } catch (\Exception $e) {
            Log::error('Error counting recent events: ' . $e->getMessage());
            return 0;
        }
    }

    public function delete(Event $event): bool
    {
        try {
            if (!$event->getId()) {
                return false;
            }

            return DB::table($this->table)->where('id', $event->getId())->delete() > 0;
        } catch (\Exception $e) {
            Log::error('Error deleting event: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteById(int $id): bool
    {
        try {
            return DB::table($this->table)->where('id', $id)->delete() > 0;
        } catch (\Exception $e) {
            Log::error('Error deleting event by ID: ' . $e->getMessage());
            return false;
        }
    }

    public function exists(int $id): bool
    {
        try {
            return DB::table($this->table)->where('id', $id)->exists();
        } catch (\Exception $e) {
            Log::error('Error checking if event exists: ' . $e->getMessage());
            return false;
        }
    }

    public function findAll(): array
    {
        try {
            $results = DB::table($this->table)->orderBy('date', 'desc')->get();
            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding all events: ' . $e->getMessage());
            return $this->getFallbackEvents('all');
        }
    }

    public function findWithPagination(int $page = 1, int $perPage = 10): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $results = DB::table($this->table)
                ->orderBy('date', 'desc')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding events with pagination: ' . $e->getMessage());
            return [];
        }
    }

    public function findUserEventsWithPagination(int $userId, int $page = 1, int $perPage = 10): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $results = DB::table($this->table)
                ->where('user_id', $userId)
                ->orderBy('date', 'desc')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding user events with pagination: ' . $e->getMessage());
            return [];
        }
    }

    // NEW METHODS NEEDED BY HOMECONTROLLER
    public function getEventsByType(string $type, int $limit = null): array
    {
        try {
            $query = DB::table($this->table)
                ->where('type', $type)
                ->orderBy('date', 'desc');

            if ($limit) {
                $query->limit($limit);
            }

            $results = $query->get();
            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error getting events by type: ' . $e->getMessage());
            return $this->getFallbackEvents($type);
        }
    }

    public function getEventsByTypeWithPagination(string $type, int $perPage = 10, int $page = 1): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $results = DB::table($this->table)
                ->where('type', $type)
                ->orderBy('date', 'desc')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error getting events by type with pagination: ' . $e->getMessage());
            return $this->getFallbackEvents($type);
        }
    }

    public function findByIdWithRelations(int $id): ?Event
    {
        try {
            // For now, this is the same as findById since we're using raw queries
            // In a full implementation, you might join with categories, users, etc.
            $data = DB::table($this->table)
                ->where('id', $id)
                ->first();
            
            if (!$data) {
                return null;
            }

            return $this->mapToEntity($data);
        } catch (\Exception $e) {
            Log::error('Error finding event by ID with relations: ' . $e->getMessage());
            return null;
        }
    }

    public function getRelatedEvents(int $categoryId, int $excludeId, int $limit = 3): array
    {
        try {
            $results = DB::table($this->table)
                ->where('categorie_id', $categoryId)
                ->where('id', '!=', $excludeId)
                ->orderBy('date', 'desc')
                ->limit($limit)
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error getting related events: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * CRITICAL FIX: Use EventDate::fromDatabase() to allow past dates from database
     */
    private function mapToEntity($data): Event
    {
        try {
            return new Event(
                new EventTitle($data->title),
                new EventDescription($data->description),
                EventDate::fromDatabase($data->date), // FIXED: Allow past dates from database
                new EventTime($data->time ?? ''),
                new EventLocation($data->location),
                new EventType($data->type),
                $data->user_id,
                $data->categorie_id,
                $data->image,
                $data->id,
                new \DateTime($data->created_at),
                new \DateTime($data->updated_at)
            );
        } catch (\Exception $e) {
            Log::error('Error mapping data to entity: ' . $e->getMessage() . ' Data: ' . json_encode($data));
            throw $e;
        }
    }

    private function mapToEntities($results): array
    {
        $entities = [];
        foreach ($results as $data) {
            try {
                $entities[] = $this->mapToEntity($data);
            } catch (\Exception $e) {
                Log::error('Error mapping entity, skipping: ' . $e->getMessage());
                // Skip problematic entities instead of failing completely
                continue;
            }
        }
        return $entities;
    }

    /**
     * Fallback data when database is unavailable
     */
    private function getFallbackEvents(string $type): array
    {
        $fallbackEvents = [];
        
        try {
            switch ($type) {
                case 'Feature':
                case 'featured':
                    $fallbackEvents = [
                        $this->createFallbackEvent(1, 'Tech Conference 2025', 'Annual technology conference', '2025-12-15', 'Feature'),
                        $this->createFallbackEvent(2, 'Business Summit', 'Leadership and innovation summit', '2025-11-20', 'Feature'),
                        $this->createFallbackEvent(3, 'Digital Marketing Workshop', 'Learn latest digital marketing trends', '2025-10-25', 'Feature'),
                        $this->createFallbackEvent(4, 'Startup Pitch Day', 'Entrepreneurs showcase their ideas', '2025-12-05', 'Feature'),
                    ];
                    break;
                    
                case 'Recent':
                case 'recent':
                    $fallbackEvents = [
                        $this->createFallbackEvent(5, 'Web Development Bootcamp', 'Intensive coding bootcamp', '2025-10-15', 'Recent'),
                        $this->createFallbackEvent(6, 'AI & Machine Learning Seminar', 'Explore the future of AI', '2025-11-10', 'Recent'),
                        $this->createFallbackEvent(7, 'Networking Mixer', 'Professional networking event', '2025-10-30', 'Recent'),
                        $this->createFallbackEvent(8, 'Product Launch Event', 'Unveiling our latest product', '2025-11-25', 'Recent'),
                        $this->createFallbackEvent(9, 'Design Thinking Workshop', 'Creative problem solving', '2025-12-10', 'Recent'),
                        $this->createFallbackEvent(10, 'Cybersecurity Conference', 'Latest in digital security', '2025-11-15', 'Recent'),
                    ];
                    break;
                    
                default:
                    $fallbackEvents = [
                        $this->createFallbackEvent(11, 'Sample Event 1', 'This is a sample event', '2025-10-20', 'General'),
                        $this->createFallbackEvent(12, 'Sample Event 2', 'Another sample event', '2025-11-05', 'General'),
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Error creating fallback events: ' . $e->getMessage());
        }
        
        return $fallbackEvents;
    }

    private function createFallbackEvent(int $id, string $title, string $description, string $date, string $type): Event
    {
        return new Event(
            new EventTitle($title),
            new EventDescription($description),
            EventDate::fromDatabase($date), // Use fromDatabase to allow any date
            new EventTime('10:00'),
            new EventLocation('Online Event'),
            new EventType($type),
            1, // user_id
            1, // category_id
            'default-event.jpg',
            $id,
            new \DateTime(),
            new \DateTime()
        );
    }
}
