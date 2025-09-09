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

class EloquentEventRepository implements EventRepositoryInterface
{
    private string $table = 'events';

    public function save(Event $event): Event
    {
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
    }

    public function findById(int $id): ?Event
    {
        $data = DB::table($this->table)->where('id', $id)->first();
        
        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function findByUserId(int $userId): array
    {
        $results = DB::table($this->table)
            ->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findByCategoryId(int $categoryId): array
    {
        $results = DB::table($this->table)
            ->where('categorie_id', $categoryId)
            ->orderBy('date', 'desc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findUpcomingEvents(): array
    {
        $results = DB::table($this->table)
            ->where('date', '>=', date('Y-m-d'))
            ->orderBy('date', 'asc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findPastEvents(): array
    {
        $results = DB::table($this->table)
            ->where('date', '<', date('Y-m-d'))
            ->orderBy('date', 'desc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findFeaturedEvents(): array
    {
        $results = DB::table($this->table)
            ->where('type', 'Feature')
            ->where('date', '>=', date('Y-m-d'))
            ->orderBy('date', 'asc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findRecentEvents(): array
    {
        $results = DB::table($this->table)
            ->where('type', 'Recent')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findEventsByDate(EventDate $date): array
    {
        $results = DB::table($this->table)
            ->where('date', $date->getFormattedDate())
            ->orderBy('time', 'asc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findEventsByDateRange(EventDate $startDate, EventDate $endDate): array
    {
        $results = DB::table($this->table)
            ->whereBetween('date', [$startDate->getFormattedDate(), $endDate->getFormattedDate()])
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findEventsByType(EventType $type): array
    {
        $results = DB::table($this->table)
            ->where('type', $type->getValue())
            ->orderBy('date', 'desc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findEventsByLocation(string $location): array
    {
        $results = DB::table($this->table)
            ->where('location', 'LIKE', '%' . $location . '%')
            ->orderBy('date', 'desc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findOnlineEvents(): array
    {
        $results = DB::table($this->table)
            ->where('location', 'LIKE', '%online%')
            ->orWhere('location', 'LIKE', '%virtual%')
            ->orWhere('location', 'LIKE', '%zoom%')
            ->orWhere('location', 'LIKE', '%meet%')
            ->orderBy('date', 'desc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findPhysicalEvents(): array
    {
        $results = DB::table($this->table)
            ->where('location', 'NOT LIKE', '%online%')
            ->where('location', 'NOT LIKE', '%virtual%')
            ->where('location', 'NOT LIKE', '%zoom%')
            ->where('location', 'NOT LIKE', '%meet%')
            ->orderBy('date', 'desc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findTodaysEvents(): array
    {
        $results = DB::table($this->table)
            ->where('date', date('Y-m-d'))
            ->orderBy('time', 'asc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findThisWeeksEvents(): array
    {
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));

        $results = DB::table($this->table)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findThisMonthsEvents(): array
    {
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');

        $results = DB::table($this->table)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function searchEvents(string $query): array
    {
        $results = DB::table($this->table)
            ->where('title', 'LIKE', '%' . $query . '%')
            ->orWhere('description', 'LIKE', '%' . $query . '%')
            ->orWhere('location', 'LIKE', '%' . $query . '%')
            ->orderBy('date', 'desc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findUserEvents(int $userId, ?EventType $type = null): array
    {
        $query = DB::table($this->table)->where('user_id', $userId);

        if ($type) {
            $query->where('type', $type->getValue());
        }

        $results = $query->orderBy('date', 'desc')->get();

        return $this->mapToEntities($results);
    }

    public function findUserUpcomingEvents(int $userId): array
    {
        $results = DB::table($this->table)
            ->where('user_id', $userId)
            ->where('date', '>=', date('Y-m-d'))
            ->orderBy('date', 'asc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function findUserPastEvents(int $userId): array
    {
        $results = DB::table($this->table)
            ->where('user_id', $userId)
            ->where('date', '<', date('Y-m-d'))
            ->orderBy('date', 'desc')
            ->get();

        return $this->mapToEntities($results);
    }

    public function countEventsByUser(int $userId): int
    {
        return DB::table($this->table)->where('user_id', $userId)->count();
    }

    public function countEventsByCategory(int $categoryId): int
    {
        return DB::table($this->table)->where('categorie_id', $categoryId)->count();
    }

    public function countUpcomingEvents(): int
    {
        return DB::table($this->table)->where('date', '>=', date('Y-m-d'))->count();
    }

    public function countPastEvents(): int
    {
        return DB::table($this->table)->where('date', '<', date('Y-m-d'))->count();
    }

    public function countFeaturedEvents(): int
    {
        return DB::table($this->table)
            ->where('type', 'Feature')
            ->where('date', '>=', date('Y-m-d'))
            ->count();
    }

    public function countRecentEvents(): int
    {
        return DB::table($this->table)->where('type', 'Recent')->count();
    }

    public function delete(Event $event): bool
    {
        if (!$event->getId()) {
            return false;
        }

        return DB::table($this->table)->where('id', $event->getId())->delete() > 0;
    }

    public function deleteById(int $id): bool
    {
        return DB::table($this->table)->where('id', $id)->delete() > 0;
    }

    public function exists(int $id): bool
    {
        return DB::table($this->table)->where('id', $id)->exists();
    }

    public function findAll(): array
    {
        $results = DB::table($this->table)->orderBy('date', 'desc')->get();
        return $this->mapToEntities($results);
    }

    public function findWithPagination(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        
        $results = DB::table($this->table)
            ->orderBy('date', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        return $this->mapToEntities($results);
    }

    public function findUserEventsWithPagination(int $userId, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        
        $results = DB::table($this->table)
            ->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        return $this->mapToEntities($results);
    }

    // NEW METHODS NEEDED BY HOMECONTROLLER
    public function getEventsByType(string $type, int $limit = null): array
    {
        $query = DB::table($this->table)
            ->where('type', $type)
            ->orderBy('date', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        $results = $query->get();
        return $this->mapToEntities($results);
    }

    public function getEventsByTypeWithPagination(string $type, int $perPage = 10, int $page = 1): array
    {
        $offset = ($page - 1) * $perPage;
        
        $results = DB::table($this->table)
            ->where('type', $type)
            ->orderBy('date', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        return $this->mapToEntities($results);
    }

    public function findByIdWithRelations(int $id): ?Event
    {
        // For now, this is the same as findById since we're using raw queries
        // In a full implementation, you might join with categories, users, etc.
        $data = DB::table($this->table)
            ->where('id', $id)
            ->first();
        
        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function getRelatedEvents(int $categoryId, int $excludeId, int $limit = 3): array
    {
        $results = DB::table($this->table)
            ->where('categorie_id', $categoryId)
            ->where('id', '!=', $excludeId)
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get();

        return $this->mapToEntities($results);
    }

    private function mapToEntity($data): Event
    {
        return new Event(
            new EventTitle($data->title),
            new EventDescription($data->description),
            new EventDate($data->date),
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
    }

    private function mapToEntities($results): array
    {
        $entities = [];
        foreach ($results as $data) {
            $entities[] = $this->mapToEntity($data);
        }
        return $entities;
    }
}
