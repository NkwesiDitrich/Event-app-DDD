<?php

namespace App\Domain\Event\Entities;

use App\Domain\Event\ValueObjects\EventTitle;
use App\Domain\Event\ValueObjects\EventDate;
use App\Domain\Event\ValueObjects\EventLocation;
use App\Domain\Event\ValueObjects\EventType;
use App\Domain\Event\ValueObjects\EventDescription;
use App\Domain\Event\ValueObjects\EventTime;
use App\Domain\Event\Events\EventCreated;
use App\Domain\Event\Events\EventUpdated;
use App\Domain\Event\Events\EventDeleted;
use InvalidArgumentException;

class Event
{
    private ?int $id;
    private EventTitle $title;
    private EventDescription $description;
    private EventDate $date;
    private EventTime $time;
    private EventLocation $location;
    private EventType $type;
    private ?string $image;
    private int $userId;
    private int $categoryId;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    private array $domainEvents = [];

    public function __construct(
        EventTitle $title,
        EventDescription $description,
        EventDate $date,
        EventTime $time,
        EventLocation $location,
        EventType $type,
        int $userId,
        int $categoryId,
        ?string $image = null,
        ?int $id = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->validateUserId($userId);
        $this->validateCategoryId($categoryId);
        
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->time = $time;
        $this->location = $location;
        $this->type = $type;
        $this->image = $image;
        $this->userId = $userId;
        $this->categoryId = $categoryId;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();

        if (!$id) {
            $this->addDomainEvent(new EventCreated($this));
        }
    }

    public static function create(
        EventTitle $title,
        EventDescription $description,
        EventDate $date,
        EventTime $time,
        EventLocation $location,
        EventType $type,
        int $userId,
        int $categoryId,
        ?string $image = null
    ): self {
        return new self(
            $title,
            $description,
            $date,
            $time,
            $location,
            $type,
            $userId,
            $categoryId,
            $image
        );
    }

    public function updateDetails(
        EventTitle $title,
        EventDescription $description,
        EventDate $date,
        EventTime $time,
        EventLocation $location,
        EventType $type,
        ?string $image = null
    ): void {
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->time = $time;
        $this->location = $location;
        $this->type = $type;
        
        if ($image !== null) {
            $this->image = $image;
        }

        $this->updatedAt = new \DateTime();
        $this->addDomainEvent(new EventUpdated($this));
    }

    public function changeImage(string $imagePath): void
    {
        $this->image = $imagePath;
        $this->updatedAt = new \DateTime();
    }

    public function removeImage(): void
    {
        $this->image = null;
        $this->updatedAt = new \DateTime();
    }

    public function markAsDeleted(): void
    {
        $this->addDomainEvent(new EventDeleted($this));
    }

    // Business Logic Methods
    public function isUpcoming(): bool
    {
        return $this->date->isUpcoming();
    }

    public function isPast(): bool
    {
        return !$this->isUpcoming();
    }

    public function isFeatured(): bool
    {
        return $this->type->isFeatured();
    }

    public function isRecent(): bool
    {
        return $this->type->isRecent();
    }

    public function belongsToUser(int $userId): bool
    {
        return $this->userId === $userId;
    }

    public function belongsToCategory(int $categoryId): bool
    {
        return $this->categoryId === $categoryId;
    }

    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    public function isTomorrow(): bool
    {
        return $this->date->isTomorrow();
    }

    public function isThisWeek(): bool
    {
        return $this->date->isThisWeek();
    }

    public function getDaysUntilEvent(): int
    {
        return $this->date->getDaysUntilEvent();
    }

    public function isOnlineEvent(): bool
    {
        return $this->location->isOnline();
    }

    public function isPhysicalEvent(): bool
    {
        return $this->location->isPhysical();
    }

    public function hasImage(): bool
    {
        return $this->image !== null && !empty($this->image);
    }

    public function getTimeOfDay(): string
    {
        return $this->time->getTimeOfDay();
    }

    public function isBusinessHours(): bool
    {
        return $this->time->isBusinessHours();
    }

    public function canBeModified(): bool
    {
        return $this->isUpcoming();
    }

    public function canBeDeleted(): bool
    {
        return $this->isUpcoming();
    }

    public function getEventSummary(): string
    {
        return sprintf(
            "%s on %s at %s (%s)",
            $this->title->getValue(),
            $this->date->getHumanReadableDate(),
            $this->location->getValue(),
            $this->type->getValue()
        );
    }

    public function getShortDescription(int $maxLength = 100): string
    {
        return $this->description->getExcerpt($maxLength);
    }

    // Validation Methods
    private function validateUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('User ID must be a positive integer');
        }
    }

    private function validateCategoryId(int $categoryId): void
    {
        if ($categoryId <= 0) {
            throw new InvalidArgumentException('Category ID must be a positive integer');
        }
    }

    // Domain Events Management
    private function addDomainEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getTitle(): EventTitle { return $this->title; }
    public function getDescription(): EventDescription { return $this->description; }
    public function getDate(): EventDate { return $this->date; }
    public function getTime(): EventTime { return $this->time; }
    public function getLocation(): EventLocation { return $this->location; }
    public function getType(): EventType { return $this->type; }
    public function getImage(): ?string { return $this->image; }
    public function getUserId(): int { return $this->userId; }
    public function getCategoryId(): int { return $this->categoryId; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }

    // For persistence layer conversion
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title->getValue(),
            'description' => $this->description->getValue(),
            'date' => $this->date->getFormattedDate(),
            'time' => $this->time->getValue(),
            'location' => $this->location->getValue(),
            'type' => $this->type->getValue(),
            'image' => $this->image,
            'user_id' => $this->userId,
            'categorie_id' => $this->categoryId,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
