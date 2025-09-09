<?php

namespace App\Application\Queries;

class GetEventQuery
{
    public int $eventId;

    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;
    }
}
