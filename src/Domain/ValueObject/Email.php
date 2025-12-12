<?php
declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidEmail;

final class Email
{
    private string $value;

    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmail("Invalid email {$email}");
        }
        $this->value = $email;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === (string)$other;
    }
}
