<?php

namespace App\Application\Commands\Registration;

class UnattendParticipantCommand
{
    public function __construct(
        public readonly int $registrationId,
        public readonly int $ownerId
    ) {}
}
