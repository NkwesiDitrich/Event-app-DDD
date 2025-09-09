<?php

namespace App\Domain\Event\Events;

use App\Domain\Event\Entities\Event;

class EventCreated
{
    public Event $event;
    public \DateTime $occurredAt;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->occurredAt = new \DateTime();
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getOccurredAt(): \DateTime
    {
        return $this->occurredAt;
    }

    public function getEventId(): ?int
    {
        return $this->event->getId();
    }

    public function getUserId(): int
    {
        return $this->event->getUserId();
    }

    public function getEventTitle(): string
    {
        return $this->event->getTitle()->getValue();
    }
}
