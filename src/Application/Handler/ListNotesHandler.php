<?php

namespace App\Application\Handler;

use App\Application\Query\ListNotesQuery;
use App\Domain\Repository\NoteRepositoryInterface;
use App\Domain\ValueObject\NoteOwner;

final readonly class ListNotesHandler
{
    public function __construct(private NoteRepositoryInterface $repository)
    {
    }

    /**
     * @return array<int, array> list of note arrays (use Note::toArray())
     */
    public function __invoke(ListNotesQuery $query): array
    {
        $owner = new NoteOwner($query->ownerId);
        $notes = $this->repository->findByOwner((string)$owner);

        $result = [];
        foreach ($notes as $note) {
            if ($note->isArchived()) {
                continue;
            }
            $result[] = $note->toArray();
        }

        return $result;
    }
}
