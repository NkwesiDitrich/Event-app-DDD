<?php

namespace App\Domain\Event\ValueObjects;

use InvalidArgumentException;

class EventType
{
    private string $value;
    
    // Based on your database enum: ['Feature','Recent']
    private const VALID_TYPES = ['Feature', 'Recent'];

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(string $value): void
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Event type cannot be empty');
        }

        if (!in_array($value, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException(
                'Invalid event type. Must be one of: ' . implode(', ', self::VALID_TYPES)
            );
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isFeatured(): bool
    {
        return $this->value === 'Feature';
    }

    public function isRecent(): bool
    {
        return $this->value === 'Recent';
    }

    public function equals(EventType $other): bool
    {
        return $this->value === $other->value;
    }

    public static function feature(): self
    {
        return new self('Feature');
    }

    public static function recent(): self
    {
        return new self('Recent');
    }

    public static function getValidTypes(): array
    {
        return self::VALID_TYPES;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
