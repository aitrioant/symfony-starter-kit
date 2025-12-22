<?php

namespace App\Infrastructure\Doctrine\Mapper;

use App\Domain\Entity\Note;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteContent;
use App\Domain\ValueObject\NoteOwner;

final readonly class NoteMapper
{
    public function toDomain(array $row): Note
    {
        $id = Id::fromString($row['id']);
        $content = new NoteContent($row['content']);
        $owner = new NoteOwner($row['owner_id']);
        $archived = $row['archived'] ?? false;
        $createdAt = new \DateTimeImmutable($row['created_at']);
        $updatedAt = new \DateTimeImmutable($row['updated_at']);

        return new Note($id, $content, $owner, $archived, $createdAt, $updatedAt);
    }

    public function toRow(Note $note): array
    {
        $row = [
            'id' => (string)$note->id(),
            'content' => (string)$note->content(),
            'owner_id' => (string)$note->owner(),
            'archived' => $note->isArchived(),
        ];

        $row['created_at'] = $note->createdAt()->format('Y-m-d H:i:s');
        $row['updated_at'] = $note->updatedAt()->format('Y-m-d H:i:s');

        return $row;
    }
}
