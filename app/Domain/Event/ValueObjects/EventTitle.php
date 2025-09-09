<?php

namespace App\Domain\Event\ValueObjects;

use InvalidArgumentException;

class EventTitle
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = trim($value);
    }

    private function validate(string $value): void
    {
        $trimmedValue = trim($value);
        
        if (empty($trimmedValue)) {
            throw new InvalidArgumentException('Event title cannot be empty');
        }

        if (strlen($trimmedValue) > 150) {
            throw new InvalidArgumentException('Event title cannot exceed 150 characters');
        }

        if (strlen($trimmedValue) < 3) {
            throw new InvalidArgumentException('Event title must be at least 3 characters long');
        }

        // Business rule: Title should not contain only special characters
        if (!preg_match('/[a-zA-Z0-9]/', $trimmedValue)) {
            throw new InvalidArgumentException('Event title must contain at least one alphanumeric character');
        }

        // Business rule: No excessive whitespace
        if (preg_match('/\s{3,}/', $trimmedValue)) {
            throw new InvalidArgumentException('Event title cannot contain excessive whitespace');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(EventTitle $other): bool
    {
        return $this->value === $other->value;
    }

    public function getLength(): int
    {
        return strlen($this->value);
    }

    public function isShort(): bool
    {
        return strlen($this->value) <= 50;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
