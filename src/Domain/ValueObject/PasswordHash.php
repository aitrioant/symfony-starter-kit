<?php

namespace App\Domain\ValueObject;

final class PasswordHash
{
    private string $hash;

    private function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public static function fromHash(string $hash): self
    {
        if ($hash === '') {
            throw new \DomainException('Empty password hash is not allowed.');
        }

        return new self($hash);
    }

    public function verify(string $plain): bool
    {
        return password_verify($plain, $this->hash);
    }

    public function toString(): string
    {
        return $this->hash;
    }
}
