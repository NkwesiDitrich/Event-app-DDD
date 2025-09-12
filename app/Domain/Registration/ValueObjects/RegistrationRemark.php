<?php

namespace App\Domain\Registration\ValueObjects;

class RegistrationRemark
{
    private string $value;

    public function __construct(string $remark)
    {
        $this->value = trim($remark);
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
