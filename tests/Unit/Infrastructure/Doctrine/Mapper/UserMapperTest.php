<?php

namespace App\Tests\Unit\Infrastructure\Doctrine\Mapper;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Infrastructure\Doctrine\Mapper\UserMapper;
use PHPUnit\Framework\TestCase;

final class UserMapperTest extends TestCase
{
    public function test_toRow_and_toDomain_roundtrip(): void
    {
        $mapper = new UserMapper();

        $id = '11111111-1111-4111-8111-111111111111';
        $email = new Email('alice@example.com');
        $hash = PasswordHash::fromHash(password_hash('secret', PASSWORD_DEFAULT));

        $user = new User($id, $email, $hash);

        $row = $mapper->toRow($user);

        $this->assertSame($id, $row['id']);
        $this->assertSame('alice@example.com', $row['email']);
        $this->assertSame($hash->toString(), $row['password_hash']);

        $reconstituted = $mapper->toDomain($row);

        $this->assertNotSame($user, $reconstituted);
        $this->assertSame($user->id(), $reconstituted->id());
        $this->assertSame((string)$user->email(), (string)$reconstituted->email());
        $this->assertTrue($reconstituted->verifyPassword('secret'));
    }
}
