<?php

namespace App\Application\Handlers;

use App\Application\Queries\GetEventQuery;
use App\Domain\Event\Entities\Event;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use InvalidArgumentException;

class GetEventHandler
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function handle(GetEventQuery $query): Event
    {
        $event = $this->eventRepository->findById($query->eventId);
        
        if (!$event) {
            throw new InvalidArgumentException('Event not found');
        }

        return $event;
    }
}
