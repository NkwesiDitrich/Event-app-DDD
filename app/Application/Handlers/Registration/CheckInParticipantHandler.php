<?php

namespace App\Application\Handlers\Registration;

use App\Application\Commands\Registration\CheckInParticipantCommand;
use App\Domain\Registration\Repositories\RegistrationRepositoryInterface;

class CheckInParticipantHandler
{
    public function __construct(
        private RegistrationRepositoryInterface $registrationRepository
    ) {}

    public function handle(CheckInParticipantCommand $command): bool
    {
        return $this->registrationRepository->updateCheckInStatus(
            $command->registrationId,
            $command->checkIn,
            $command->ownerId
        );
    }
}
