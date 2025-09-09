<?php

namespace App\Application\Commands;

class UpdateEventCommand
{
    public int $eventId;
    public string $title;
    public string $description;
    public string $date;
    public string $time;
    public string $location;
    public string $type;
    public int $userId;
    public ?string $image;

    public function __construct(
        int $eventId,
        string $title,
        string $description,
        string $date,
        string $time,
        string $location,
        string $type,
        int $userId,
        ?string $image = null
    ) {
        $this->eventId = $eventId;
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->time = $time;
        $this->location = $location;
        $this->type = $type;
        $this->userId = $userId;
        $this->image = $image;
    }
}
