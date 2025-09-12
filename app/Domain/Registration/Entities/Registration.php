<?php

namespace App\Domain\Registration\Entities;

use App\Domain\Registration\ValueObjects\RegistrationDate;
use App\Domain\Registration\ValueObjects\ParticipantName;
use App\Domain\Registration\ValueObjects\ParticipantMobile;
use App\Domain\Registration\ValueObjects\ParticipantEmail;
use App\Domain\Registration\ValueObjects\RegistrationRemark;

class Registration
{
    private int $id;
    private RegistrationDate $date;
    private ParticipantName $name;
    private ParticipantMobile $mobile;
    private ?ParticipantEmail $email;
    private ?RegistrationRemark $remark;
    private int $eventId;
    private int $userId;
    private bool $checkedIn;
    private ?\DateTime $checkedInAt;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    // Additional properties for display purposes
    public ?string $eventTitle = null;
    public ?string $participantUserName = null;

    public function __construct(
        int $id,
        RegistrationDate $date,
        ParticipantName $name,
        ParticipantMobile $mobile,
        ?ParticipantEmail $email,
        ?RegistrationRemark $remark,
        int $eventId,
        int $userId,
        bool $checkedIn = false,
        ?\DateTime $checkedInAt = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->name = $name;
        $this->mobile = $mobile;
        $this->email = $email;
        $this->remark = $remark;
        $this->eventId = $eventId;
        $this->userId = $userId;
        $this->checkedIn = $checkedIn;
        $this->checkedInAt = $checkedInAt;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getDate(): RegistrationDate
    {
        return $this->date;
    }

    public function getName(): ParticipantName
    {
        return $this->name;
    }

    public function getMobile(): ParticipantMobile
    {
        return $this->mobile;
    }

    public function getEmail(): ?ParticipantEmail
    {
        return $this->email;
    }

    public function getRemark(): ?RegistrationRemark
    {
        return $this->remark;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function isCheckedIn(): bool
    {
        return $this->checkedIn;
    }

    public function getCheckedInAt(): ?\DateTime
    {
        return $this->checkedInAt;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    // Business methods
    public function checkIn(): void
    {
        if ($this->checkedIn) {
            throw new \DomainException('Participant is already checked in');
        }

        $this->checkedIn = true;
        $this->checkedInAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function checkOut(): void
    {
        if (!$this->checkedIn) {
            throw new \DomainException('Participant is not checked in');
        }

        $this->checkedIn = false;
        $this->checkedInAt = null;
        $this->updatedAt = new \DateTime();
    }

    // Helper methods for display
    public function getEventTitle(): ?string
    {
        return $this->eventTitle;
    }

    public function setEventTitle(?string $eventTitle): void
    {
        $this->eventTitle = $eventTitle;
    }

    public function getParticipantUserName(): ?string
    {
        return $this->participantUserName;
    }

    public function setParticipantUserName(?string $participantUserName): void
    {
        $this->participantUserName = $participantUserName;
    }

    public function getFormattedDate(): string
    {
        return $this->date->getFormattedDate();
    }

    public function getFormattedCreatedAt(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    public function getCheckInStatus(): string
    {
        return $this->checkedIn ? 'Checked In' : 'Not Checked In';
    }

    public function getCheckInStatusBadge(): string
    {
        return $this->checkedIn 
            ? '<span class="badge bg-success">Checked In</span>' 
            : '<span class="badge bg-warning">Not Checked In</span>';
    }
}
