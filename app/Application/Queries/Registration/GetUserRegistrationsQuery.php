<?php

namespace App\Application\Queries\Registration;

class GetUserRegistrationsQuery
{
    public function __construct(
        public readonly int $ownerId,
        public readonly ?int $eventId = null
    ) {}
}
