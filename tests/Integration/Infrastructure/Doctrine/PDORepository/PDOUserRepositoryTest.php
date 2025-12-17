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

    public function test_save_and_find(): void
    {
        $id = '22222222-2222-4222-8222-222222222222';
        $email = new Email('bob@example.com');
        $plain = 'integration-secret';
        $hash = PasswordHash::fromHash(password_hash($plain, PASSWORD_DEFAULT));

        $user = new User(Id::fromString($id), $email, $hash);

        $this->repo->save($user);

        $byId = $this->repo->findById($id);
        $this->assertNotNull($byId);
        $this->assertSame($id, $byId->id());
        $this->assertSame('bob@example.com', (string)$byId->email());
        $this->assertTrue($byId->verifyPassword($plain));

        $byEmail = $this->repo->findByEmail('bob@example.com');
        $this->assertNotNull($byEmail);
        $this->assertSame($id, $byEmail->id());
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
