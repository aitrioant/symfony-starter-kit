<?php

namespace App\Tests\Unit\Application\Handler;

use App\Application\Command\RegisterUserCommand;
use App\Application\Handler\RegisterUser;
use App\Application\Security\PasswordHasherInterface;
use App\Domain\Entity\User;
use App\Domain\Exception\UserAlreadyExists;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\PasswordHash;
use PHPUnit\Framework\TestCase;

final class RegisterUserTest extends TestCase
{
    public function test_invoke_hashes_password_and_saves_user(): void
    {
        $id = Id::new();
        $email = 'user@example.com';
        $plainPassword = 'secret';
        $hashedPassword = 'hashed-secret';

        $hasher = $this->createMock(PasswordHasherInterface::class);
        $hasher->expects($this->once())
            ->method('hashPassword')
            ->with($this->equalTo($plainPassword))
            ->willReturn($hashedPassword);

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findByEmail')
            ->with($this->equalTo($email))
            ->willReturn(null);

        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($user) use ($id, $email, $hashedPassword) {
                if (!$user instanceof User) {
                    return false;
                }

                return $user->id() === (string)$id
                    && $user->email() === $email
                    && $user->passwordHash()->toString() === $hashedPassword;
            }));

        $command = new RegisterUserCommand($id, $email, $plainPassword);
        $handler = new RegisterUser($repository, $hasher);
        $handler->__invoke($command);
    }

    public function test_invoke_throws_when_email_already_exists(): void
    {
        $existingId = Id::fromString('22222222-2222-4222-8222-222222222222');
        $email = 'user@example.com';
        $plainPassword = 'secret';

        $existingUser = new User($existingId, new Email($email), PasswordHash::fromHash('existing-hash'));

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findByEmail')
            ->with($this->equalTo($email))
            ->willReturn($existingUser);

        $hasher = $this->createMock(PasswordHasherInterface::class);
        $hasher->expects($this->never())->method('hashPassword');


        $this->expectException(UserAlreadyExists::class);

        $command = new RegisterUserCommand($existingId, $email, $plainPassword);
        $handler = new RegisterUser($repository, $hasher);
        $handler->__invoke($command);
    }
}
