<?php

namespace App\Tests\Integration\Infrastructure\Doctrine\PDORepository;

use App\Domain\Entity\User;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\PasswordHash;
use App\Infrastructure\Doctrine\Mapper\UserMapper;
use App\Infrastructure\Doctrine\PDORepository\PDOUserRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class PDOUserRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PDOUserRepository $repo;

    public function test_find_by_id_returns_null_when_missing(): void
    {
        $this->assertNull($this->repo->findById('00000000-0000-4000-8000-000000000000'));
    }

    public function test_find_by_email_returns_null_when_missing(): void
    {
        $this->assertNull($this->repo->findByEmail('nobody@example.com'));
    }

    public function test_save_and_find_by_id_returns_persisted_user(): void
    {
        $id = '22222222-2222-4222-8222-222222222222';
        $plain = 'integration-secret';
        $user = new User(
            Id::fromString($id),
            new Email('bob@example.com'),
            PasswordHash::fromHash(password_hash($plain, PASSWORD_DEFAULT))
        );

        $this->repo->save($user);

        $found = $this->repo->findById($id);
        $this->assertNotNull($found);
        $this->assertSame($id, $found->id());
        $this->assertSame('bob@example.com', (string)$found->email());
        $this->assertTrue($found->verifyPassword($plain));
    }

    public function test_save_and_find_by_email_returns_persisted_user(): void
    {
        $id = '33333333-3333-4333-8333-333333333333';
        $user = new User(
            Id::fromString($id),
            new Email('alice@example.com'),
            PasswordHash::fromHash(password_hash('pw', PASSWORD_DEFAULT))
        );

        $this->repo->save($user);

        $found = $this->repo->findByEmail('alice@example.com');
        $this->assertNotNull($found);
        $this->assertSame($id, $found->id());
        $this->assertSame('alice@example.com', (string)$found->email());
    }

    public function test_save_twice_with_same_id_updates_without_duplicating(): void
    {
        $id = '44444444-4444-4444-8444-444444444444';

        $original = new User(
            Id::fromString($id),
            new Email('original@example.com'),
            PasswordHash::fromHash(password_hash('first', PASSWORD_DEFAULT))
        );
        $this->repo->save($original);

        $updated = new User(
            Id::fromString($id),
            new Email('updated@example.com'),
            PasswordHash::fromHash(password_hash('second', PASSWORD_DEFAULT))
        );
        $this->repo->save($updated);

        $count = (int)$this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $this->assertSame(1, $count);

        $found = $this->repo->findById($id);
        $this->assertNotNull($found);
        $this->assertSame('updated@example.com', (string)$found->email());
        $this->assertTrue($found->verifyPassword('second'));
        $this->assertFalse($found->verifyPassword('first'));
    }

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec(<<<'SQL'
CREATE TABLE users (
  id TEXT PRIMARY KEY,
  email TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL
);
SQL
        );

        $this->repo = new PDOUserRepository($this->pdo, new UserMapper());
    }
}