<?php

namespace App\Domain\Event\Repositories;

use App\Domain\Event\Entities\Event;
use App\Domain\Event\ValueObjects\EventDate;
use App\Domain\Event\ValueObjects\EventType;

interface EventRepositoryInterface
{
    public function save(Event $event): Event;
    public function findById(int $id): ?Event;
    public function findByUserId(int $userId): array;
    public function findByCategoryId(int $categoryId): array;
    public function findUpcomingEvents(): array;
    public function findPastEvents(): array;
    public function findFeaturedEvents(): array;
    public function findRecentEvents(): array;
    public function findEventsByDate(EventDate $date): array;
    public function findEventsByDateRange(EventDate $startDate, EventDate $endDate): array;
    public function findEventsByType(EventType $type): array;
    public function findEventsByLocation(string $location): array;
    public function findOnlineEvents(): array;
    public function findPhysicalEvents(): array;
    public function findTodaysEvents(): array;
    public function findThisWeeksEvents(): array;
    public function findThisMonthsEvents(): array;
    public function searchEvents(string $query): array;
    public function findUserEvents(int $userId, ?EventType $type = null): array;
    public function findUserUpcomingEvents(int $userId): array;
    public function findUserPastEvents(int $userId): array;
    public function countEventsByUser(int $userId): int;
    public function countEventsByCategory(int $categoryId): int;
    public function countUpcomingEvents(): int;
    public function countPastEvents(): int;
    public function countFeaturedEvents(): int;
    public function countRecentEvents(): int;
    public function delete(Event $event): bool;
    public function deleteById(int $id): bool;
    public function exists(int $id): bool;
    public function findAll(): array;
    public function findWithPagination(int $page = 1, int $perPage = 10): array;
    public function findUserEventsWithPagination(int $userId, int $page = 1, int $perPage = 10): array;

    public function getEventsByType(string $type, int $limit = null): array;
    public function getEventsByTypeWithPagination(string $type, int $perPage = 10, int $page = 1): array;
    public function findByIdWithRelations(int $id): ?Event;
    public function getRelatedEvents(int $categoryId, int $excludeId, int $limit = 3): array;
}
