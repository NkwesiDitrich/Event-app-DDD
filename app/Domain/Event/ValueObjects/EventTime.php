<?php

namespace App\Domain\Event\ValueObjects;

use InvalidArgumentException;
use DateTime;

class EventTime
{
    private string $value;

    public function __construct(?string $value = null)
    {
        if ($value !== null) {
            $this->validate($value);
            $this->value = $this->sanitize($value);
        } else {
            $this->value = '';
        }
    }

    private function validate(string $value): void
    {
        $trimmedValue = trim($value);
        
        if (strlen($trimmedValue) > 100) {
            throw new InvalidArgumentException('Event time cannot exceed 100 characters');
        }

        // If not empty, validate time format
        if (!empty($trimmedValue)) {
            // Accept various time formats: HH:MM, H:MM AM/PM, etc.
            if (!$this->isValidTimeFormat($trimmedValue)) {
                throw new InvalidArgumentException('Invalid time format. Use formats like: 14:30, 2:30 PM, 14:30:00');
            }
        }
    }

    private function isValidTimeFormat(string $time): bool
    {
        // Pattern for various time formats
        $patterns = [
            '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/',                    // HH:MM (24-hour)
            '/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/',         // HH:MM:SS (24-hour)
            '/^(1[0-2]|0?[1-9]):[0-5][0-9]\s?(AM|PM)$/i',            // H:MM AM/PM
            '/^(1[0-2]|0?[1-9]):[0-5][0-9]:[0-5][0-9]\s?(AM|PM)$/i', // H:MM:SS AM/PM
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($time))) {
                return true;
            }
        }

        return false;
    }

    private function sanitize(string $value): string
    {
        return trim($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function to24HourFormat(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        try {
            $dateTime = DateTime::createFromFormat('H:i', $this->value);
            if ($dateTime) {
                return $dateTime->format('H:i');
            }

            // Try 12-hour format
            $dateTime = DateTime::createFromFormat('g:i A', $this->value);
            if ($dateTime) {
                return $dateTime->format('H:i');
            }

            // Try with seconds
            $dateTime = DateTime::createFromFormat('H:i:s', $this->value);
            if ($dateTime) {
                return $dateTime->format('H:i');
            }

            return $this->value; // Return original if conversion fails
        } catch (\Exception $e) {
            return $this->value;
        }
    }

    public function to12HourFormat(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        try {
            $dateTime = DateTime::createFromFormat('H:i', $this->value);
            if ($dateTime) {
                return $dateTime->format('g:i A');
            }

            // Try if already in 12-hour format
            $dateTime = DateTime::createFromFormat('g:i A', $this->value);
            if ($dateTime) {
                return $dateTime->format('g:i A');
            }

            return $this->value; // Return original if conversion fails
        } catch (\Exception $e) {
            return $this->value;
        }
    }

    public function getHour(): ?int
    {
        if ($this->isEmpty()) {
            return null;
        }

        try {
            $time24 = $this->to24HourFormat();
            $parts = explode(':', $time24);
            return (int) $parts[0];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getMinute(): ?int
    {
        if ($this->isEmpty()) {
            return null;
        }

        try {
            $time24 = $this->to24HourFormat();
            $parts = explode(':', $time24);
            return (int) $parts[1];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isMorning(): bool
    {
        $hour = $this->getHour();
        return $hour !== null && $hour >= 6 && $hour < 12;
    }

    public function isAfternoon(): bool
    {
        $hour = $this->getHour();
        return $hour !== null && $hour >= 12 && $hour < 18;
    }

    public function isEvening(): bool
    {
        $hour = $this->getHour();
        return $hour !== null && $hour >= 18 && $hour < 22;
    }

    public function isNight(): bool
    {
        $hour = $this->getHour();
        return $hour !== null && ($hour >= 22 || $hour < 6);
    }

    public function getTimeOfDay(): string
    {
        if ($this->isMorning()) return 'Morning';
        if ($this->isAfternoon()) return 'Afternoon';
        if ($this->isEvening()) return 'Evening';
        if ($this->isNight()) return 'Night';
        return 'Unknown';
    }

    public function isBusinessHours(): bool
    {
        $hour = $this->getHour();
        return $hour !== null && $hour >= 9 && $hour <= 17;
    }

    public function equals(EventTime $other): bool
    {
        return $this->to24HourFormat() === $other->to24HourFormat();
    }

    public function isBefore(EventTime $other): bool
    {
        if ($this->isEmpty() || $other->isEmpty()) {
            return false;
        }

        $thisHour = $this->getHour();
        $thisMinute = $this->getMinute();
        $otherHour = $other->getHour();
        $otherMinute = $other->getMinute();

        if ($thisHour === null || $otherHour === null) {
            return false;
        }

        return ($thisHour < $otherHour) || 
               ($thisHour === $otherHour && $thisMinute < $otherMinute);
    }

    public function isAfter(EventTime $other): bool
    {
        return !$this->isBefore($other) && !$this->equals($other);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
