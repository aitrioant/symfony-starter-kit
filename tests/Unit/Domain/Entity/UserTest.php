<?php

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\User;
use App\Domain\Exception\InvalidEmail;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\PasswordHash;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function test_constructor_throws_on_invalid_email(): void
    {
        $this->expectException(InvalidEmail::class);

        $id = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $invalidEmail = 'not-an-email';
        $hash = password_hash('secret', PASSWORD_DEFAULT);

        new User(Id::fromString($id), new Email($invalidEmail), PasswordHash::fromHash($hash));
    }

    public function test_constructor_throws_on_empty_password_hash(): void
    {
        $this->expectException(\DomainException::class);

        $id = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
        $email = 'user@example.com';
        $emptyHash = '';

        new User(Id::fromString($id), new Email($email), PasswordHash::fromHash($emptyHash));
    }

    public function test_change_email_returns_new_instance_with_new_email(): void
    {
        $id = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';
        $email = 'user@example.com';
        $hash = password_hash('secret', PASSWORD_DEFAULT);

        $user = new User(Id::fromString($id), new Email($email), PasswordHash::fromHash($hash));

        $newEmail = 'new@example.com';
        $newUser = $user->changeEmail($newEmail);

        $this->assertNotSame($user, $newUser);
        $this->assertSame($email, $user->email());
        $this->assertSame($newEmail, $newUser->email());
    }

    public function test_change_email_throws_on_invalid_email(): void
    {
        $id = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';
        $hash = password_hash('secret', PASSWORD_DEFAULT);

        $user = new User(Id::fromString($id), new Email('user@example.com'), PasswordHash::fromHash($hash));

        $this->expectException(InvalidEmail::class);
        $user->changeEmail('invalid-email');
    }

    public function test_verify_password_behaviour(): void
    {
        $id = 'dddddddd-dddd-4ddd-8ddd-dddddddddddd';
        $email = 'user@example.com';
        $plain = 'super-secret';
        $hash = password_hash($plain, PASSWORD_DEFAULT);

        $user = new User(Id::fromString($id), new Email($email), PasswordHash::fromHash($hash));

        $this->assertTrue($user->verifyPassword($plain));
        $this->assertFalse($user->verifyPassword('wrong-password'));
    }
}
