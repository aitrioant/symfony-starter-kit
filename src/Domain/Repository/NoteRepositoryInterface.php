<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Note;

interface NoteRepositoryInterface
{
    public function save(Note $note): void;

    public function findById(string $id): ?Note;

    /**
     * @return Note[]
     */
    public function findByOwner(string $ownerId): array;
}
