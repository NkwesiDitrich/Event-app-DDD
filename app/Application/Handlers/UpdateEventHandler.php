<?php

namespace App\Application\Handlers;

use App\Application\Commands\UpdateEventCommand;
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

class UpdateEventHandler
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

    public function handle(UpdateEventCommand $command): Event
    {
        // Find existing event
        $event = $this->eventRepository->findById($command->eventId);
        if (!$event) {
            throw new InvalidArgumentException('Event not found');
        }

        // Check if user can modify this event
        if (!$this->eventDomainService->canEventBeModified($event, $command->userId)) {
            throw new InvalidArgumentException('You cannot modify this event');
        }

        // Create Value Objects with validation
        $title = new EventTitle($command->title);
        $description = new EventDescription($command->description);
        $date = new EventDate($command->date);
        $time = new EventTime($command->time);
        $location = new EventLocation($command->location);
        $type = new EventType($command->type);

        // Check business rules for updated event
        if (!$this->eventDomainService->canEventBeScheduledAtLocation($location, $date)) {
            // Allow if it's the same event at the same location/date
            $isSameLocationAndDate = $event->getLocation()->getValue() === $location->getValue() 
                && $event->getDate()->isSameDay($date);
            
            if (!$isSameLocationAndDate) {
                throw new InvalidArgumentException('Another event is already scheduled at this location on this date');
            }
        }

        // Update event details
        $event->updateDetails(
            $title,
            $description,
            $date,
            $time,
            $location,
            $type,
            $command->image
        );

        // Save the updated event
        $savedEvent = $this->eventRepository->save($event);

        // Process domain events
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
