<?php

namespace App\Domain\Event\ValueObjects;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;

class EventDate
{
    private DateTime $value;

    public function __construct(string $date, ?string $timezone = null)
    {
        $this->validate($date);
        $this->value = $this->createDateTime($date, $timezone);
    }

    private function validate(string $date): void
    {
        // Check if date format is valid (Y-m-d)
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        
        if (!$dateTime || $dateTime->format('Y-m-d') !== $date) {
            throw new InvalidArgumentException('Invalid date format. Expected format: YYYY-MM-DD (e.g., 2025-12-31)');
        }

        // Business rule: Event date cannot be in the past
        $today = new DateTime('today');
        if ($dateTime < $today) {
            throw new InvalidArgumentException('Event date cannot be in the past. Please select a future date.');
        }

        // Business rule: Event cannot be scheduled more than 2 years in advance
        $maxFutureDate = new DateTime('+2 years');
        if ($dateTime > $maxFutureDate) {
            throw new InvalidArgumentException('Event cannot be scheduled more than 2 years in advance');
        }
    }

    private function createDateTime(string $date, ?string $timezone): DateTime
    {
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        
        if ($timezone) {
            try {
                $dateTime->setTimezone(new DateTimeZone($timezone));
            } catch (\Exception $e) {
                // If timezone is invalid, use default
                $dateTime->setTimezone(new DateTimeZone('UTC'));
            }
        }
        
        return $dateTime;
    }

    public function getValue(): DateTime
    {
        return clone $this->value; // Return clone to maintain immutability
    }

    public function getFormattedDate(string $format = 'Y-m-d'): string
    {
        return $this->value->format($format);
    }

    public function getHumanReadableDate(): string
    {
        return $this->value->format('F j, Y'); // e.g., "December 31, 2025"
    }

    public function isToday(): bool
    {
        $today = new DateTime('today');
        return $this->value->format('Y-m-d') === $today->format('Y-m-d');
    }

    public function isTomorrow(): bool
    {
        $tomorrow = new DateTime('tomorrow');
        return $this->value->format('Y-m-d') === $tomorrow->format('Y-m-d');
    }

    public function isThisWeek(): bool
    {
        $startOfWeek = new DateTime('monday this week');
        $endOfWeek = new DateTime('sunday this week');
        
        return $this->value >= $startOfWeek && $this->value <= $endOfWeek;
    }

    public function isThisMonth(): bool
    {
        $now = new DateTime();
        return $this->value->format('Y-m') === $now->format('Y-m');
    }

    public function getDaysUntilEvent(): int
    {
        $today = new DateTime('today');
        $diff = $today->diff($this->value);
        
        return $diff->invert ? -$diff->days : $diff->days;
    }

    public function isUpcoming(): bool
    {
        return $this->value > new DateTime();
    }

    public function equals(EventDate $other): bool
    {
        return $this->value->format('Y-m-d') === $other->value->format('Y-m-d');
    }

    public function isAfter(EventDate $other): bool
    {
        return $this->value > $other->value;
    }

    public function isBefore(EventDate $other): bool
    {
        return $this->value < $other->value;
    }

    public function __toString(): string
    {
        return $this->getFormattedDate();
    }
}
