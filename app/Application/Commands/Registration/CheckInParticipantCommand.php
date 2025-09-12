<?php

namespace App\Application\Commands\Registration;

class CheckInParticipantCommand
{
    public function __construct(
        public readonly int $registrationId,
        public readonly int $ownerId,
        public readonly bool $checkIn = true
    ) {}
}
