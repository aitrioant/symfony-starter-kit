<?php

namespace App\Tests\Unit\Infrastructure\Doctrine\Mapper;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\PasswordHash;
use App\Infrastructure\Doctrine\Mapper\UserMapper;
use PHPUnit\Framework\TestCase;

final class UserMapperTest extends TestCase
{
    public function test_toRow_extracts_user_fields(): void
    {
        $id = '11111111-1111-4111-8111-111111111111';
        $hash = PasswordHash::fromHash(password_hash('secret', PASSWORD_DEFAULT));
        $user = new User(Id::fromString($id), new Email('alice@example.com'), $hash);

        $row = (new UserMapper())->toRow($user);

        $this->assertSame($id, $row['id']);
        $this->assertSame('alice@example.com', $row['email']);
        $this->assertSame($hash->toString(), $row['password_hash']);
    }

    public function test_toDomain_builds_user_from_row(): void
    {
        $id = '11111111-1111-4111-8111-111111111111';
        $hash = PasswordHash::fromHash(password_hash('secret', PASSWORD_DEFAULT));

        $row = [
            'id' => $id,
            'email' => 'alice@example.com',
            'password_hash' => $hash->toString(),
        ];

        $user = (new UserMapper())->toDomain($row);

        $this->assertSame($id, $user->id());
        $this->assertSame('alice@example.com', (string)$user->email());
        $this->assertTrue($user->verifyPassword('secret'));
    }
}