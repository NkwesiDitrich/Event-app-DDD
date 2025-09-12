<?php

namespace App\Application\Handlers\Registration;

use App\Application\Queries\Registration\GetUserRegistrationsQuery;
use App\Domain\Registration\Repositories\RegistrationRepositoryInterface;

class GetUserRegistrationsHandler
{
    public function __construct(
        private RegistrationRepositoryInterface $registrationRepository
    ) {}

    public function handle(GetUserRegistrationsQuery $query): array
    {
        if ($query->eventId) {
            return $this->registrationRepository->findByEventIdAndOwnerId(
                $query->eventId,
                $query->ownerId
            );
        }

        return $this->registrationRepository->findByEventOwnerId($query->ownerId);
    }
}
