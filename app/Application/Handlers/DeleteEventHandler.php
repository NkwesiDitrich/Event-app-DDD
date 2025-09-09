<?php

namespace App\Application\Handlers;

use App\Application\Commands\DeleteEventCommand;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Domain\Event\Services\EventDomainService;
use InvalidArgumentException;

class DeleteEventHandler
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

    public function handle(DeleteEventCommand $command): bool
    {
        // Find existing event
        $event = $this->eventRepository->findById($command->eventId);
        if (!$event) {
            throw new InvalidArgumentException('Event not found');
        }

        // Check if user can delete this event
        if (!$this->eventDomainService->canEventBeDeleted($event, $command->userId)) {
            throw new InvalidArgumentException('You cannot delete this event');
        }

        // Mark as deleted (for domain events)
        $event->markAsDeleted();

        // Process domain events before deletion
        $this->processDomainEvents($event);

        // Delete the event
        return $this->eventRepository->delete($event);
    }

    private function processDomainEvents($event): void
    {
        $domainEvents = $event->getDomainEvents();
        
        foreach ($domainEvents as $domainEvent) {
            // Here you would dispatch domain events to event handlers
            // For now, we'll just clear them
        }
        
        $event->clearDomainEvents();
    }
}
