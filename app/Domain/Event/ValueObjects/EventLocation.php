<?php

namespace App\Domain\Event\ValueObjects;

use InvalidArgumentException;

class EventLocation
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $this->sanitize($value);
    }

    private function validate(string $value): void
    {
        $trimmedValue = trim($value);
        
        if (empty($trimmedValue)) {
            throw new InvalidArgumentException('Event location cannot be empty');
        }

        if (strlen($trimmedValue) > 150) {
            throw new InvalidArgumentException('Event location cannot exceed 150 characters');
        }

        if (strlen($trimmedValue) < 2) {
            throw new InvalidArgumentException('Event location must be at least 2 characters long');
        }

        // Business rule: Location should contain meaningful content
        if (!preg_match('/[a-zA-Z0-9]/', $trimmedValue)) {
            throw new InvalidArgumentException('Event location must contain at least one alphanumeric character');
        }

        // Business rule: No excessive special characters
        if (preg_match('/[!@#$%^&*()]{3,}/', $trimmedValue)) {
            throw new InvalidArgumentException('Event location contains too many special characters');
        }

        // Business rule: No excessive whitespace
        if (preg_match('/\s{4,}/', $trimmedValue)) {
            throw new InvalidArgumentException('Event location cannot contain excessive whitespace');
        }
    }

    private function sanitize(string $value): string
    {
        // Trim whitespace
        $sanitized = trim($value);
        
        // Replace multiple spaces with single space
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        
        // Capitalize first letter of each word for consistency
        $sanitized = ucwords(strtolower($sanitized));
        
        return $sanitized;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getOriginalCase(): string
    {
        return $this->value;
    }

    public function getUpperCase(): string
    {
        return strtoupper($this->value);
    }

    public function getLowerCase(): string
    {
        return strtolower($this->value);
    }

    public function getLength(): int
    {
        return strlen($this->value);
    }

    public function isShort(): bool
    {
        return strlen($this->value) <= 30;
    }

    public function isLong(): bool
    {
        return strlen($this->value) > 100;
    }

    public function containsKeyword(string $keyword): bool
    {
        return stripos($this->value, $keyword) !== false;
    }

    public function isOnline(): bool
    {
        $onlineKeywords = ['online', 'virtual', 'zoom', 'teams', 'meet', 'webinar', 'remote'];
        
        foreach ($onlineKeywords as $keyword) {
            if ($this->containsKeyword($keyword)) {
                return true;
            }
        }
        
        return false;
    }

    public function isPhysical(): bool
    {
        return !$this->isOnline();
    }

    public function getLocationWords(): array
    {
        return explode(' ', $this->value);
    }

    public function getWordCount(): int
    {
        return count($this->getLocationWords());
    }

    public function equals(EventLocation $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    public function contains(string $substring): bool
    {
        return stripos($this->value, $substring) !== false;
    }

    public function startsWith(string $prefix): bool
    {
        return stripos($this->value, $prefix) === 0;
    }

    public function endsWith(string $suffix): bool
    {
        return substr_compare($this->value, $suffix, -strlen($suffix), strlen($suffix), true) === 0;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
