<?php

namespace App\Application\Queries;

class GetUserEventsQuery
{
    public int $userId;
    public ?string $type;
    public int $page;
    public int $perPage;

    public function __construct(int $userId, ?string $type = null, int $page = 1, int $perPage = 10)
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->page = $page;
        $this->perPage = $perPage;
    }
}
