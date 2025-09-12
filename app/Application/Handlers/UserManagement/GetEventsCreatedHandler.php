<?php

namespace App\Application\Handlers\UserManagement;

use App\Application\Queries\UserManagement\GetEventsCreatedQuery;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Domain\Registration\Repositories\RegistrationRepositoryInterface;

class GetEventsCreatedHandler
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
        private readonly RegistrationRepositoryInterface $registrationRepository
    ) {}

    public function handle(GetEventsCreatedQuery $query): array
    {
        // Get events created by the user
        $events = $this->eventRepository->findByUserIdWithFilters(
            $query->userId,
            $query->status,
            $query->page,
            $query->perPage
        );

        // Get total count for pagination
        $totalCount = $this->eventRepository->countByUserIdWithFilters(
            $query->userId,
            $query->status
        );

        // Enhance events with participant statistics
        $eventsWithStats = [];
        foreach ($events as $event) {
            $participantStats = $this->registrationRepository->getEventParticipantStats($event->getId());
            
            $eventsWithStats[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle()->getValue(),
                'description' => $event->getDescription()->getValue(),
                'date' => $event->getDate()->getFormattedDate(),
                'time' => $event->getTime()->getValue(),
                'location' => $event->getLocation()->getValue(),
                'type' => $event->getType()->getValue(),
                'image' => $event->getImage(),
                'category_id' => $event->getCategoryId(),
                'created_at' => $event->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $event->getUpdatedAt()->format('Y-m-d H:i:s'),
                'is_upcoming' => $event->isUpcoming(),
                'is_past' => $event->isPast(),
                'is_today' => $event->isToday(),
                'days_until_event' => $event->getDaysUntilEvent(),
                'participant_stats' => [
                    'total_participants' => $participantStats['total_participants'],
                    'checked_in' => $participantStats['checked_in'],
                    'not_checked_in' => $participantStats['not_checked_in'],
                    'check_in_rate' => $participantStats['total_participants'] > 0 
                        ? round(($participantStats['checked_in'] / $participantStats['total_participants']) * 100, 1)
                        : 0
                ]
            ];
        }

        // Get overall statistics for all user's events
        $userEventIds = array_map(fn($event) => $event->getId(), $events);
        $overallStats = !empty($userEventIds) 
            ? $this->registrationRepository->getParticipantsStatistics($userEventIds)
            : [
                'total_participants' => 0,
                'checked_in' => 0,
                'not_checked_in' => 0
            ];

        return [
            'events' => $eventsWithStats,
            'total' => $totalCount,
            'statistics' => [
                'total_events' => $totalCount,
                'upcoming_events' => count(array_filter($eventsWithStats, fn($e) => $e['is_upcoming'])),
                'past_events' => count(array_filter($eventsWithStats, fn($e) => $e['is_past'])),
                'today_events' => count(array_filter($eventsWithStats, fn($e) => $e['is_today'])),
                'overall_participants' => $overallStats
            ],
            'pagination' => [
                'current_page' => $query->page,
                'per_page' => $query->perPage,
                'total_pages' => ceil($totalCount / $query->perPage),
                'has_more' => ($query->page * $query->perPage) < $totalCount
            ]
        ];
    }
}
