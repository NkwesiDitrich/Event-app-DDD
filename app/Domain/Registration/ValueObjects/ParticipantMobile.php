<?php

namespace App\Domain\Registration\ValueObjects;

class ParticipantMobile
{
    private string $value;

    public function __construct(string $mobile)
    {
        if (empty(trim($mobile))) {
            throw new \InvalidArgumentException('Mobile number cannot be empty');
        }

        if (strlen($mobile) > 50) {
            throw new \InvalidArgumentException('Mobile number cannot exceed 50 characters');
        }

        $this->value = trim($mobile);
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
