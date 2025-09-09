<?php

namespace App\Application\Commands;

class CreateEventCommand
{
    public string $title;
    public string $description;
    public string $date;
    public string $time;
    public string $location;
    public string $type;
    public int $userId;
    public int $categoryId;
    public ?string $image;

    public function __construct(
        string $title,
        string $description,
        string $date,
        string $time,
        string $location,
        string $type,
        int $userId,
        int $categoryId,
        ?string $image = null
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->time = $time;
        $this->location = $location;
        $this->type = $type;
        $this->userId = $userId;
        $this->categoryId = $categoryId;
        $this->image = $image;
    }
}
