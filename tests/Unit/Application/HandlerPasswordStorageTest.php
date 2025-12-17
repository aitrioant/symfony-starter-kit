<?php

namespace App\Tests\Unit\Application;

use App\Application\Command\RegisterUserCommand;
use App\Application\Handler\RegisterUser;
use App\Application\Security\PasswordHasherInterface;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class HandlerPasswordStorageTest extends TestCase
{
    public function test_handler_saves_hashed_password_and_verifies(): void
    {
        $email = 'secure@example.com';
        $plain = 'super-secret';

        $hasher = self::makeHasher();

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findByEmail')
            ->with($this->equalTo($email))
            ->willReturn(null);

        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($user) use ($email, $plain) {
                if (!$user instanceof User) {
                    return false;
                }

                if ($user->email() !== $email) {
                    return false;
                }

                $hash = $user->passwordHash()->toString();

                // must not store raw password
                if ($hash === $plain) {
                    return false;
                }

                // must verify against plain password
                return password_verify($plain, $hash);
            }));

        $command = new RegisterUserCommand(Id::new(), $email, $plain);
        $handler = new RegisterUser($repository, $hasher);
        $handler->__invoke($command);
    }

    private static function makeHasher(): PasswordHasherInterface
    {
        return new class implements PasswordHasherInterface {
            public function hashPassword(string $plainPassword): string
            {
                return password_hash($plainPassword, PASSWORD_DEFAULT);
            }
        };
    }
}
