<?php

namespace App\Tests\Unit\Application\Handler;

use App\Application\Handler\RegisterUser;
use App\Application\Security\PasswordHasherInterface;
use App\Domain\Entity\User;
use App\Domain\Exception\UserAlreadyExists;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use PHPUnit\Framework\TestCase;

final class RegisterUserTest extends TestCase
{
    public function test_invoke_hashes_password_and_saves_user(): void
    {
        $id = '11111111-1111-4111-8111-111111111111';
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

                return $user->id() === $id
                    && $user->email() === $email
                    && $user->passwordHash()->toString() === $hashedPassword;
            }));

        $handler = new RegisterUser($repository, $hasher);
        $handler->__invoke($id, $email, $plainPassword);
    }

    public function test_invoke_throws_when_email_already_exists(): void
    {
        $existingId = '22222222-2222-4222-8222-222222222222';
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

        $handler = new RegisterUser($repository, $hasher);

        $this->expectException(UserAlreadyExists::class);

        $handler->__invoke($existingId, $email, $plainPassword);
    }
}
