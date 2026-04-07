<?php

namespace App\Infrastructure\Doctrine\PDORepository;

use App\Domain\Entity\Note;
use App\Infrastructure\Doctrine\Mapper\NoteMapper;
use PDO;

final readonly class PDONoteRepository
{
    public function __construct(private PDO $pdo, private NoteMapper $mapper)
    {
    }

    public function save(Note $note): void
    {
        $row = $this->mapper->toRow($note);

        $stmt = $this->pdo->prepare('SELECT COUNT(1) FROM notes WHERE id = :id');
        $stmt->execute([':id' => $row['id']]);
        $exists = (bool)$stmt->fetchColumn();

        if ($exists) {
            $sql = 'UPDATE notes SET content = :content, owner_id = :owner_id, archived = :archived, updated_at = COALESCE(:updated_at, NOW()) WHERE id = :id';
        } else {
            $sql = 'INSERT INTO notes (id, content, owner_id, archived, created_at, updated_at) VALUES (:id, :content, :owner_id, :archived, COALESCE(:created_at, NOW()), COALESCE(:updated_at, NOW()))';
        }

        $stmt = $this->pdo->prepare($sql);
        $params = [
            ':id' => $row['id'],
            ':content' => $row['content'],
            ':owner_id' => $row['owner_id'],
            ':archived' => $row['archived'],
            ':created_at' => $row['created_at'] ?? null,
            ':updated_at' => $row['updated_at'] ?? null,
        ];

        $stmt->execute($params);
    }

    public function findById(string $id): ?Note
    {
        $stmt = $this->pdo->prepare('SELECT id, content, owner_id, archived, created_at, updated_at FROM notes WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->mapper->toDomain($row);
    }

    /**
     * @return Note[]
     */
    public function findByOwner(string $ownerId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, content, owner_id, archived, created_at, updated_at FROM notes WHERE owner_id = :owner_id ORDER BY created_at DESC');
        $stmt->execute([':owner_id' => $ownerId]);

        $notes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notes[] = $this->mapper->toDomain($row);
        }

        return $notes;
    }
}
