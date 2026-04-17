<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Doctrine\PDORepository;

use App\Domain\Entity\Note;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteContent;
use App\Domain\ValueObject\NoteOwner;
use App\Infrastructure\Doctrine\Mapper\NoteMapper;
use App\Infrastructure\Doctrine\PDORepository\PDONoteRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;

final class PDONoteRepositoryTest extends TestCase
{
    private PDO $pdo;
    private PDONoteRepository $repo;

    public function test_save_inserts_new_note_and_find_by_id_returns_it(): void
    {
        $id = (string)Id::new();
        $created = new DateTimeImmutable('2023-01-01 10:00:00');
        $note = new Note(Id::fromString($id), new NoteContent('hello'), new NoteOwner('owner-x'), false, $created, $created);

        $this->repo->save($note);

        $found = $this->repo->findById($id);
        $this->assertNotNull($found);
        $this->assertSame($id, (string)$found->id());
        $this->assertSame('hello', (string)$found->content());
        $this->assertSame('owner-x', (string)$found->owner());
        $this->assertFalse($found->isArchived());
        $this->assertSame($created->format('Y-m-d H:i:s'), $found->createdAt()->format('Y-m-d H:i:s'));

        $this->assertSame(1, $this->countRows());
    }

    private function countRows(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM notes')->fetchColumn();
    }

    public function test_save_updates_existing_note_without_duplicating(): void
    {
        $id = (string)Id::new();
        $created = new DateTimeImmutable('2023-01-01 10:00:00');
        $original = new Note(Id::fromString($id), new NoteContent('original'), new NoteOwner('owner-y'), false, $created, $created);

        $this->repo->save($original);

        $updatedAt = new DateTimeImmutable('2023-01-02 12:00:00');
        $updated = new Note(Id::fromString($id), new NoteContent('changed'), new NoteOwner('owner-y'), true, $created, $updatedAt);

        $this->repo->save($updated);

        $this->assertSame(1, $this->countRows());

        $found = $this->repo->findById($id);
        $this->assertNotNull($found);
        $this->assertSame('changed', (string)$found->content());
        $this->assertTrue($found->isArchived());
        $this->assertSame($updatedAt->format('Y-m-d H:i:s'), $found->updatedAt()->format('Y-m-d H:i:s'));
    }

    public function test_find_by_id_returns_null_when_missing(): void
    {
        $this->assertNull($this->repo->findById((string)Id::new()));
    }

    public function test_find_by_owner_returns_only_that_owners_notes(): void
    {
        $ownerA = 'owner-a';
        $ownerB = 'owner-b';

        $this->repo->save(new Note(Id::new(), new NoteContent('a1'), new NoteOwner($ownerA)));
        $this->repo->save(new Note(Id::new(), new NoteContent('a2'), new NoteOwner($ownerA)));
        $this->repo->save(new Note(Id::new(), new NoteContent('b1'), new NoteOwner($ownerB)));

        $aNotes = $this->repo->findByOwner($ownerA);
        $this->assertCount(2, $aNotes);
        foreach ($aNotes as $n) {
            $this->assertSame($ownerA, (string)$n->owner());
        }

        $bNotes = $this->repo->findByOwner($ownerB);
        $this->assertCount(1, $bNotes);
        $this->assertSame('b1', (string)$bNotes[0]->content());
    }

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // The production SQL uses NOW() inside COALESCE; register it so SQLite can resolve the symbol.
        $this->pdo->sqliteCreateFunction('now', static fn(): string => date('Y-m-d H:i:s'));

        $this->pdo->exec(<<<'SQL'
CREATE TABLE notes (
  id TEXT PRIMARY KEY,
  content TEXT NOT NULL,
  owner_id TEXT NOT NULL,
  archived INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL
);
SQL
        );

        $this->repo = new PDONoteRepository($this->pdo, new NoteMapper());
    }
}
