<?php

namespace App\Domain\Event\Services;

use App\Domain\Event\Entities\Event;
use App\Domain\Event\ValueObjects\EventDate;
use App\Domain\Event\ValueObjects\EventLocation;
use App\Domain\Event\ValueObjects\EventType;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use InvalidArgumentException;

class EventDomainService
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function canUserCreateEvent(int $userId): bool
    {
        $userEventCount = $this->eventRepository->countEventsByUser($userId);
        
        // Business rule: Users can create maximum 50 events
        return $userEventCount < 50;
    }

    public function canEventBeScheduledAtLocation(EventLocation $location, EventDate $date): bool
    {
        // Business rule: Only one physical event per location per day
        if ($location->isPhysical()) {
            $eventsAtLocation = $this->eventRepository->findEventsByLocation($location->getValue());
            
            foreach ($eventsAtLocation as $event) {
                if ($event->getDate()->isSameDay($date)) {
                    return false;
                }
            }
        }
        
        return true;
    }

    public function canEventBePromotedToFeatured(Event $event): bool
    {
        // Business rule: Only upcoming events can be featured
        if (!$event->isUpcoming()) {
            return false;
        }

        // Business rule: Event must be at least 3 days in the future
        if ($event->getDaysUntilEvent() < 3) {
            return false;
        }

        // Business rule: Maximum 10 featured events at any time
        $featuredCount = $this->eventRepository->countFeaturedEvents();
        if ($featuredCount >= 10 && !$event->isFeatured()) {
            return false;
        }

        return true;
    }

    public function promoteEventToFeatured(Event $event): Event
    {
        if (!$this->canEventBePromotedToFeatured($event)) {
            throw new InvalidArgumentException('Event cannot be promoted to featured');
        }

        $featuredType = new EventType('Feature');
        $event->updateDetails(
            $event->getTitle(),
            $event->getDescription(),
            $event->getDate(),
            $event->getTime(),
            $event->getLocation(),
            $featuredType,
            $event->getImage()
        );

        return $event;
    }

    public function demoteEventFromFeatured(Event $event): Event
    {
        if (!$event->isFeatured()) {
            throw new InvalidArgumentException('Event is not featured');
        }

        $recentType = new EventType('Recent');
        $event->updateDetails(
            $event->getTitle(),
            $event->getDescription(),
            $event->getDate(),
            $event->getTime(),
            $event->getLocation(),
            $recentType,
            $event->getImage()
        );

        return $event;
    }

    public function isEventConflicting(Event $event, int $userId): bool
    {
        $userEvents = $this->eventRepository->findUserEvents($userId);
        
        foreach ($userEvents as $existingEvent) {
            // Skip the same event (for updates)
            if ($existingEvent->getId() === $event->getId()) {
                continue;
            }

            // Check for same day conflicts
            if ($existingEvent->getDate()->isSameDay($event->getDate())) {
                // Business rule: User cannot have more than 3 events on the same day
                $sameDayEvents = array_filter($userEvents, function($e) use ($event) {
                    return $e->getDate()->isSameDay($event->getDate());
                });

                if (count($sameDayEvents) >= 3) {
                    return true;
                }
            }
        }

        return false;
    }

    public function calculateEventPriority(Event $event): int
    {
        $priority = 0;

        // Featured events get higher priority
        if ($event->isFeatured()) {
            $priority += 100;
        }

        // Upcoming events get priority based on how soon they are
        if ($event->isUpcoming()) {
            $daysUntil = $event->getDaysUntilEvent();
            if ($daysUntil <= 1) {
                $priority += 50; // Today/Tomorrow
            } elseif ($daysUntil <= 7) {
                $priority += 30; // This week
            } elseif ($daysUntil <= 30) {
                $priority += 10; // This month
            }
        }

        // Online events get slight priority (easier to attend)
        if ($event->isOnlineEvent()) {
            $priority += 5;
        }

        return $priority;
    }

    public function getEventRecommendations(int $userId, int $limit = 5): array
    {
        $userEvents = $this->eventRepository->findUserEvents($userId);
        $allUpcomingEvents = $this->eventRepository->findUpcomingEvents();

        // Remove user's own events
        $recommendations = array_filter($allUpcomingEvents, function($event) use ($userId) {
            return $event->getUserId() !== $userId;
        });

        // Sort by priority
        usort($recommendations, function($a, $b) {
            return $this->calculateEventPriority($b) - $this->calculateEventPriority($a);
        });

        return array_slice($recommendations, 0, $limit);
    }

    public function canEventBeDeleted(Event $event, int $userId): bool
    {
        // Business rule: Only event owner can delete
        if (!$event->belongsToUser($userId)) {
            return false;
        }

        // Business rule: Cannot delete past events
        if ($event->isPast()) {
            return false;
        }

        // Business rule: Cannot delete events starting within 2 hours
        if ($event->getDaysUntilEvent() === 0) {
            // Additional time check would be needed here
            // For now, we'll allow same-day deletions
        }

        return true;
    }

    public function canEventBeModified(Event $event, int $userId): bool
    {
        // Business rule: Only event owner can modify
        if (!$event->belongsToUser($userId)) {
            return false;
        }

        // Business rule: Cannot modify past events
        if ($event->isPast()) {
            return false;
        }

        return true;
    }

    public function getEventStatistics(): array
    {
        return [
            'total_events' => count($this->eventRepository->findAll()),
            'upcoming_events' => $this->eventRepository->countUpcomingEvents(),
            'past_events' => $this->eventRepository->countPastEvents(),
            'featured_events' => $this->eventRepository->countFeaturedEvents(),
            'recent_events' => $this->eventRepository->countRecentEvents(),
            'todays_events' => count($this->eventRepository->findTodaysEvents()),
            'this_weeks_events' => count($this->eventRepository->findThisWeeksEvents()),
            'online_events' => count($this->eventRepository->findOnlineEvents()),
            'physical_events' => count($this->eventRepository->findPhysicalEvents()),
        ];
    }
}
