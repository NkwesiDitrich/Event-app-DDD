<?php

namespace App\Application\Handlers\Registration;

use App\Application\Commands\Registration\UnattendParticipantCommand;
use App\Domain\Registration\Repositories\RegistrationRepositoryInterface;

class UnattendParticipantHandler
{
    public function __construct(
        private RegistrationRepositoryInterface $registrationRepository
    ) {}

    public function handle(UnattendParticipantCommand $command): bool
    {
        return $this->registrationRepository->deleteByIdAndOwnerId(
            $command->registrationId,
            $command->ownerId
        );
    }
}
