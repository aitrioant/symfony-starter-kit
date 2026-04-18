<?php

namespace App\Application\Handler;

use App\Application\Command\CreateNoteCommand;
use App\Application\Service\UserExistsChecker;
use App\Domain\Entity\Note;
use App\Domain\Exception\OwnerNotFound;
use App\Domain\Repository\NoteRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteContent;
use App\Domain\ValueObject\NoteOwner;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateNoteHandler
{
    public function __construct(
        private NoteRepositoryInterface $repository,
        private UserExistsChecker       $userExists,
    )
    {
    }

    public function __invoke(CreateNoteCommand $command): void
    {
        if (!$this->userExists->exists($command->ownerId)) {
            throw new OwnerNotFound(sprintf('Owner "%s" does not exist.', $command->ownerId));
        }

        $id = Id::fromString($command->id);
        $content = new NoteContent($command->content);
        $owner = new NoteOwner($command->ownerId);

        $note = new Note($id, $content, $owner);

        $this->repository->save($note);
    }
}