<?php

namespace App\Domain\Registration\ValueObjects;

class RegistrationDate
{
    private \DateTime $value;

    public function __construct(string $date)
    {
        $this->value = new \DateTime($date);
    }

    public function getValue(): \DateTime
    {
        return $this->value;
    }

    public function getFormattedDate(): string
    {
        return $this->value->format('Y-m-d');
    }

    public function getHumanReadableDate(): string
    {
        return $this->value->format('F j, Y');
    }

    public function __toString(): string
    {
        return $this->getFormattedDate();
    }
}
