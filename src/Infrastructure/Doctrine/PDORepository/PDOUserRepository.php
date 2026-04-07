<?php

namespace App\Infrastructure\Doctrine\PDORepository;

use App\Domain\Entity\User;
use App\Infrastructure\Doctrine\Mapper\UserMapper;
use PDO;

final readonly class PDOUserRepository
{
    public function __construct(private PDO $pdo, private UserMapper $mapper)
    {
    }

    public function save(User $user): void
    {
        $row = $this->mapper->toRow($user);

        $sql = 'INSERT OR REPLACE INTO users (id, email, password_hash) VALUES (:id, :email, :password_hash)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $row['id'],
            ':email' => $row['email'],
            ':password_hash' => $row['password_hash'],
        ]);
    }

    public function findById(string $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT id, email, password_hash FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapper->toDomain($row);
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT id, email, password_hash FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $this->mapper->toDomain($row);
    }
}
