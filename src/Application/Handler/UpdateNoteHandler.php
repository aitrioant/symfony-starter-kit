<?php

namespace App\Application\Handler;

use App\Application\Command\UpdateNoteCommand;
use App\Domain\Exception\NoteNotFound;
use App\Domain\Repository\NoteRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteContent;
use App\Domain\ValueObject\NoteOwner;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateNoteHandler
{
    public function __construct(private NoteRepositoryInterface $repository)
    {
    }

    public function __invoke(UpdateNoteCommand $command): void
    {
        $id = Id::fromString($command->id);

        $note = $this->repository->findById((string)$id);
        if ($note === null) {
            throw new NoteNotFound('Note not found.');
        }

        $content = new NoteContent($command->content);
        $actor = new NoteOwner($command->ownerId);

        $note->updateContent($content, $actor);

        $this->repository->save($note);
    }
}
