<?php

namespace App\Application\Handler;

use App\Application\Command\ArchiveNoteCommand;
use App\Domain\Exception\NoteNotFound;
use App\Domain\Repository\NoteRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteOwner;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ArchiveNoteHandler
{
    public function __construct(private NoteRepositoryInterface $repository)
    {
    }

    public function __invoke(ArchiveNoteCommand $command): void
    {
        $id = Id::fromString($command->id);

        $note = $this->repository->findById((string)$id);
        if ($note === null) {
            throw new NoteNotFound('Note not found.');
        }

        $actor = new NoteOwner($command->ownerId);

        $note->archive($actor);

        $this->repository->save($note);
    }
}
