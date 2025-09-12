<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Registration\Entities\Registration;
use App\Domain\Registration\Repositories\RegistrationRepositoryInterface;
use App\Domain\Registration\ValueObjects\RegistrationDate;
use App\Domain\Registration\ValueObjects\ParticipantName;
use App\Domain\Registration\ValueObjects\ParticipantMobile;
use App\Domain\Registration\ValueObjects\ParticipantEmail;
use App\Domain\Registration\ValueObjects\RegistrationRemark;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentRegistrationRepository implements RegistrationRepositoryInterface
{
    public function findById(int $id): ?Registration
    {
        try {
            $data = DB::table('registrations')
                ->leftJoin('events', 'registrations.event_id', '=', 'events.id')
                ->leftJoin('users', 'registrations.user_id', '=', 'users.id')
                ->select(
                    'registrations.*',
                    'events.title as event_title',
                    'users.firstName as participant_user_name'
                )
                ->where('registrations.id', $id)
                ->first();

            return $data ? $this->mapToEntity($data) : null;
        } catch (\Exception $e) {
            Log::error('Error finding registration by ID: ' . $e->getMessage());
            return null;
        }
    }

    public function findByEventOwnerId(int $ownerId): array
    {
        try {
            $results = DB::table('registrations')
                ->join('events', 'registrations.event_id', '=', 'events.id')
                ->leftJoin('users', 'registrations.user_id', '=', 'users.id')
                ->select(
                    'registrations.*',
                    'events.title as event_title',
                    'users.firstName as participant_user_name'
                )
                ->where('events.user_id', $ownerId)
                ->orderBy('registrations.created_at', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding registrations by event owner ID: ' . $e->getMessage());
            return [];
        }
    }

    public function findByEventIdAndOwnerId(int $eventId, int $ownerId): array
    {
        try {
            $results = DB::table('registrations')
                ->join('events', 'registrations.event_id', '=', 'events.id')
                ->leftJoin('users', 'registrations.user_id', '=', 'users.id')
                ->select(
                    'registrations.*',
                    'events.title as event_title',
                    'users.firstName as participant_user_name'
                )
                ->where('registrations.event_id', $eventId)
                ->where('events.user_id', $ownerId)
                ->orderBy('registrations.created_at', 'desc')
                ->get();

            return $this->mapToEntities($results);
        } catch (\Exception $e) {
            Log::error('Error finding registrations by event ID and owner ID: ' . $e->getMessage());
            return [];
        }
    }

    public function updateCheckInStatus(int $registrationId, bool $checkedIn, int $ownerId): bool
    {
        try {
            // First verify that the user owns the event for this registration
            if (!$this->userOwnsEventForRegistration($registrationId, $ownerId)) {
                return false;
            }

            $updateData = [
                'checked_in' => $checkedIn,
                'updated_at' => now()
            ];

            if ($checkedIn) {
                $updateData['checked_in_at'] = now();
            } else {
                $updateData['checked_in_at'] = null;
            }

            $affected = DB::table('registrations')
                ->where('id', $registrationId)
                ->update($updateData);

            return $affected > 0;
        } catch (\Exception $e) {
            Log::error('Error updating check-in status: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteByIdAndOwnerId(int $registrationId, int $ownerId): bool
    {
        try {
            // First verify that the user owns the event for this registration
            if (!$this->userOwnsEventForRegistration($registrationId, $ownerId)) {
                return false;
            }

            $affected = DB::table('registrations')
                ->join('events', 'registrations.event_id', '=', 'events.id')
                ->where('registrations.id', $registrationId)
                ->where('events.user_id', $ownerId)
                ->delete();

            return $affected > 0;
        } catch (\Exception $e) {
            Log::error('Error deleting registration: ' . $e->getMessage());
            return false;
        }
    }

    public function save(Registration $registration): Registration
    {
        try {
            $data = [
                'date' => $registration->getDate()->getFormattedDate(),
                'name' => $registration->getName()->getValue(),
                'mobile' => $registration->getMobile()->getValue(),
                'email' => $registration->getEmail() ? $registration->getEmail()->getValue() : null,
                'remark' => $registration->getRemark() ? $registration->getRemark()->getValue() : null,
                'event_id' => $registration->getEventId(),
                'user_id' => $registration->getUserId(),
                'checked_in' => $registration->isCheckedIn(),
                'checked_in_at' => $registration->getCheckedInAt(),
                'updated_at' => now()
            ];

            if ($registration->getId() > 0) {
                // Update existing registration
                DB::table('registrations')
                    ->where('id', $registration->getId())
                    ->update($data);
            } else {
                // Create new registration
                $data['created_at'] = now();
                $id = DB::table('registrations')->insertGetId($data);
                
                // Return updated entity with new ID
                return $this->findById($id);
            }

            return $registration;
        } catch (\Exception $e) {
            Log::error('Error saving registration: ' . $e->getMessage());
            throw $e;
        }
    }

    public function userOwnsEventForRegistration(int $registrationId, int $userId): bool
    {
        try {
            $count = DB::table('registrations')
                ->join('events', 'registrations.event_id', '=', 'events.id')
                ->where('registrations.id', $registrationId)
                ->where('events.user_id', $userId)
                ->count();

            return $count > 0;
        } catch (\Exception $e) {
            Log::error('Error checking event ownership: ' . $e->getMessage());
            return false;
        }
    }

    private function mapToEntity($data): Registration
    {
        $registration = new Registration(
            id: $data->id,
            date: new RegistrationDate($data->date),
            name: new ParticipantName($data->name),
            mobile: new ParticipantMobile($data->mobile),
            email: $data->email ? new ParticipantEmail($data->email) : null,
            remark: $data->remark ? new RegistrationRemark($data->remark) : null,
            eventId: $data->event_id,
            userId: $data->user_id,
            checkedIn: (bool) ($data->checked_in ?? false),
            checkedInAt: $data->checked_in_at ? new \DateTime($data->checked_in_at) : null,
            createdAt: new \DateTime($data->created_at),
            updatedAt: new \DateTime($data->updated_at)
        );

        // Set additional display properties
        if (isset($data->event_title)) {
            $registration->setEventTitle($data->event_title);
        }
        
        if (isset($data->participant_user_name)) {
            $registration->setParticipantUserName($data->participant_user_name);
        }

        return $registration;
    }

    private function mapToEntities($results): array
    {
        $entities = [];
        
        foreach ($results as $data) {
            try {
                $entities[] = $this->mapToEntity($data);
            } catch (\Exception $e) {
                Log::error('Error mapping registration entity, skipping: ' . $e->getMessage());
                continue;
            }
        }
        
        return $entities;
    }
}
