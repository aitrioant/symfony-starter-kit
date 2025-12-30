<?php

namespace App\Infrastructure\Doctrine;

use App\Domain\Entity\Note;
use App\Domain\Repository\NoteRepositoryInterface;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteContent;
use App\Domain\ValueObject\NoteOwner;
use App\Infrastructure\Doctrine\Domain\Entity\OrmNote;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final readonly class DoctrineNoteRepository implements NoteRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findById(string $id): ?Note
    {
        /** @var OrmNote|null $orm */
        $orm = $this->getRepository()->find($id);
        return $orm ? $this->toDomain($orm) : null;
    }

    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(OrmNote::class);
    }

    private function toDomain(OrmNote $orm): Note
    {
        $note = new Note(
            Id::fromString($orm->getId()),
            new NoteContent($orm->getContent()),
            new NoteOwner($orm->getOwnerId())
        );

        if ($orm->isArchived()) {
            // archive using the owner to keep domain rules consistent
            $note->archive(new NoteOwner($orm->getOwnerId()));
        }

        return $note;
    }

    /**
     * @return Note[]
     */
    public function findByOwner(string $ownerId): array
    {
        // adjust field name to your OrmNote mapping (ownerId / owner / owner_id)
        $orms = $this->em->getRepository(OrmNote::class)->findBy(['ownerId' => $ownerId], ['createdAt' => 'DESC']);

        $notes = [];
        foreach ($orms as $orm) {
            $notes[] = $this->toDomain($orm);
        }

        return $notes;
    }

    public function save(Note $note): void
    {
        $repo = $this->getRepository();

        // Try to find existing ORM entity by primary key.
        $orm = $repo->find((string)$note->id())
            ?? new OrmNote((string)$note->id(), (string)$note->content(), (string)$note->owner());

        // Map domain -> orm. Adjust setter names to match OrmNote implementation.
        $orm->setContent((string)$note->content());
        $orm->setOwnerId((string)$note->owner());
        $orm->setArchived($note->isArchived());

        $this->em->persist($orm);
        $this->em->flush();
    }
}
