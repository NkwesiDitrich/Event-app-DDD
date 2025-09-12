<?php

namespace App\Domain\Registration\ValueObjects;

class ParticipantEmail
{
    private string $value;

    public function __construct(string $email)
    {
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        if (strlen($email) > 150) {
            throw new \InvalidArgumentException('Email cannot exceed 150 characters');
        }

        $this->value = trim($email);
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
