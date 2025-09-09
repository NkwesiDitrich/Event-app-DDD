<?php

namespace App\Application\Handlers;

use App\Application\Commands\CreateEventCommand;
use App\Domain\Event\Entities\Event;
use App\Domain\Event\ValueObjects\EventTitle;
use App\Domain\Event\ValueObjects\EventDescription;
use App\Domain\Event\ValueObjects\EventDate;
use App\Domain\Event\ValueObjects\EventTime;
use App\Domain\Event\ValueObjects\EventLocation;
use App\Domain\Event\ValueObjects\EventType;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Domain\Event\Services\EventDomainService;
use InvalidArgumentException;

class CreateEventHandler
{
    private EventRepositoryInterface $eventRepository;
    private EventDomainService $eventDomainService;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        EventDomainService $eventDomainService
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventDomainService = $eventDomainService;
    }

    public function handle(CreateEventCommand $command): Event
    {
        // Check if user can create event
        if (!$this->eventDomainService->canUserCreateEvent($command->userId)) {
            throw new InvalidArgumentException('User has reached maximum event limit');
        }

        // Create Value Objects with validation
        $title = new EventTitle($command->title);
        $description = new EventDescription($command->description);
        $date = new EventDate($command->date);
        $time = new EventTime($command->time);
        $location = new EventLocation($command->location);
        $type = new EventType($command->type);

        // Create Event Entity
        $event = Event::create(
            $title,
            $description,
            $date,
            $time,
            $location,
            $type,
            $command->userId,
            $command->categoryId,
            $command->image
        );

        // Check business rules
        if (!$this->eventDomainService->canEventBeScheduledAtLocation($location, $date)) {
            throw new InvalidArgumentException('Another event is already scheduled at this location on this date');
        }

        if ($this->eventDomainService->isEventConflicting($event, $command->userId)) {
            throw new InvalidArgumentException('This event conflicts with your existing events');
        }

        // Save the event
        $savedEvent = $this->eventRepository->save($event);

        // Process domain events (if needed)
        $this->processDomainEvents($savedEvent);

        return $savedEvent;
    }

    private function processDomainEvents(Event $event): void
    {
        $domainEvents = $event->getDomainEvents();
        
        foreach ($domainEvents as $domainEvent) {
            // Here you would dispatch domain events to event handlers
            // For now, we'll just clear them
        }
        
        $event->clearDomainEvents();
    }
}
