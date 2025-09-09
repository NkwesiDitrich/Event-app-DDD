<?php

namespace App\Application\Handlers;

use App\Application\Queries\GetUserEventsQuery;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Domain\Event\ValueObjects\EventType;

class GetUserEventsHandler
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function handle(GetUserEventsQuery $query): array
    {
        $type = null;
        if ($query->type) {
            $type = new EventType($query->type);
        }

        if ($query->page > 1 || $query->perPage !== 10) {
            return $this->eventRepository->findUserEventsWithPagination(
                $query->userId,
                $query->page,
                $query->perPage
            );
        }

        return $this->eventRepository->findUserEvents($query->userId, $type);
    }
}
