<?php

namespace App\Application\Command;

use App\Application\Security\PasswordHasherInterface;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

class RegisterUser
{
    public function __construct(
        private UserRepositoryInterface $users,
        private PasswordHasherInterface $hasher
    )
    {
    }

    public function __invoke(string $id, string $email, string $plainPassword): void
    {
        if ($this->users->findByEmail($email) !== null) {
            throw new \RuntimeException('Email already used.');
        }
        $passwordHash = $this->hasher->hashPasswordFor($id, $email, $plainPassword);
        $user = new User($id, $email, $passwordHash);
        $this->users->save($user);
    }
}
