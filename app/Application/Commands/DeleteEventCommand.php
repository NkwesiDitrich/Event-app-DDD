<?php

namespace App\Application\Commands;

class DeleteEventCommand
{
    public int $eventId;
    public int $userId;

    public function __construct(int $eventId, int $userId)
    {
        $this->eventId = $eventId;
        $this->userId = $userId;
    }
}
