<?php

namespace App\Application\Handler;

use App\Application\Command\RegisterUserCommand;
use App\Application\Security\PasswordHasherInterface;
use App\Domain\Entity\User;
use App\Domain\Exception\UserAlreadyExists;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RegisterUser
{
    public function __construct(
        private UserRepositoryInterface $users,
        private PasswordHasherInterface $hasher
    )
    {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        if ($this->users->findByEmail($command->email) !== null) {
            throw new UserAlreadyExists("A user with email {$command->email} already exists.");
        }

        if ($command->plainPassword === '') {
            throw new \DomainException('Empty password hash is not allowed.');
        }

        $passwordHash = $this->hasher->hashPassword($command->plainPassword);
        $user = new User($command->id, new Email($command->email), PasswordHash::fromHash($passwordHash));
        $this->users->save($user);
    }
}
