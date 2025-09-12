<?php

namespace App\Application\Queries\UserManagement;

class GetEventsCreatedQuery
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $status = null, // 'upcoming', 'past', 'all'
        public readonly int $page = 1,
        public readonly int $perPage = 10
    ) {}
}
