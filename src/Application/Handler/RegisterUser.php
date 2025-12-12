<?php

namespace App\Application\Handler;

use App\Application\Security\PasswordHasherInterface;
use App\Domain\Entity\User;
use App\Domain\Exception\UserAlreadyExists;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;

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
            throw new UserAlreadyExists("A user with email {$email} already exists.");
        }
        $passwordHash = $this->hasher->hashPassword($plainPassword);
        $user = new User($id, new Email($email), PasswordHash::fromHash($passwordHash));
        $this->users->save($user);
    }
}
