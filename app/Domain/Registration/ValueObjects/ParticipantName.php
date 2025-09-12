<?php

namespace App\Domain\Registration\ValueObjects;

class ParticipantName
{
    private string $value;

    public function __construct(string $name)
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Participant name cannot be empty');
        }

        if (strlen($name) > 255) {
            throw new \InvalidArgumentException('Participant name cannot exceed 255 characters');
        }

        $this->value = trim($name);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
