<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;

final class User
{
    private string $id;
    private Email $email;
    private PasswordHash $passwordHash;

    public function __construct(string $id, Email $email, PasswordHash $passwordHash)
    {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function email(): string
    {
        return (string)$this->email;
    }

    public function changeEmail(string $newEmail): self
    {
        return new self($this->id, new Email($newEmail), $this->passwordHash);
    }

    public function verifyPassword(string $plain): bool
    {
        return $this->passwordHash()->verify($plain);
    }

    public function passwordHash(): PasswordHash
    {
        return $this->passwordHash;
    }
}
