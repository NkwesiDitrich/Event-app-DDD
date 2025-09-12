<?php

namespace App\Domain\Registration\Repositories;

use App\Domain\Registration\Entities\Registration;

interface RegistrationRepositoryInterface
{
    /**
     * Find registration by ID
     */
    public function findById(int $id): ?Registration;

    /**
     * Get all registrations for events owned by a specific user (admin)
     */
    public function findByEventOwnerId(int $ownerId): array;

    /**
     * Get registrations for a specific event (only if the user owns the event)
     */
    public function findByEventIdAndOwnerId(int $eventId, int $ownerId): array;

    /**
     * Update registration check-in status
     */
    public function updateCheckInStatus(int $registrationId, bool $checkedIn, int $ownerId): bool;

    /**
     * Delete registration (unattend) - only if user owns the event
     */
    public function deleteByIdAndOwnerId(int $registrationId, int $ownerId): bool;

    /**
     * Save registration entity
     */
    public function save(Registration $registration): Registration;

    /**
     * Check if user owns the event for this registration
     */
    public function userOwnsEventForRegistration(int $registrationId, int $userId): bool;
}
