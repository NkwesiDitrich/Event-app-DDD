<?php

namespace App\Application\Queries\UserManagement;

class GetEventParticipantsQuery
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $eventId = null,
        public readonly ?string $status = null, // 'checked_in', 'not_checked_in', 'all'
        public readonly int $page = 1,
        public readonly int $perPage = 10
    ) {}
}
