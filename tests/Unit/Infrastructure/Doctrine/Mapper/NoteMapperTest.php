<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Doctrine\Mapper;

use App\Domain\Entity\Note;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteContent;
use App\Domain\ValueObject\NoteOwner;
use App\Infrastructure\Doctrine\Mapper\NoteMapper;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class NoteMapperTest extends TestCase
{
    public function testToDomainBuildsNoteFromRow(): void
    {
        $created = new DateTimeImmutable('2022-02-01 12:00:00');
        $updated = new DateTimeImmutable('2022-02-02 13:00:00');
        $id = (string)Id::new();

        $row = [
            'id' => $id,
            'content' => 'hello',
            'owner_id' => 'u1',
            'archived' => true,
            'created_at' => $created->format('Y-m-d H:i:s'),
            'updated_at' => $updated->format('Y-m-d H:i:s'),
        ];

        $note = (new NoteMapper())->toDomain($row);

        $this->assertSame($id, (string)$note->id());
        $this->assertSame('hello', (string)$note->content());
        $this->assertSame('u1', (string)$note->owner());
        $this->assertTrue($note->isArchived());
        $this->assertSame($created->format('Y-m-d H:i:s'), $note->createdAt()->format('Y-m-d H:i:s'));
        $this->assertSame($updated->format('Y-m-d H:i:s'), $note->updatedAt()->format('Y-m-d H:i:s'));
    }

    public function testToRowProducesRowFromArchivedNote(): void
    {
        $created = new DateTimeImmutable('2022-02-01 12:00:00');
        $updated = new DateTimeImmutable('2022-02-02 13:00:00');
        $id = Id::new();

        $note = new Note($id, new NoteContent('hello'), new NoteOwner('u1'), true, $created, $updated);

        $row = (new NoteMapper())->toRow($note);

        $this->assertSame((string)$id, $row['id']);
        $this->assertSame('hello', $row['content']);
        $this->assertSame('u1', $row['owner_id']);
        $this->assertTrue($row['archived']);
        $this->assertSame($created->format('Y-m-d H:i:s'), $row['created_at']);
        $this->assertSame($updated->format('Y-m-d H:i:s'), $row['updated_at']);
    }

    public function testToRowPreservesArchivedFalse(): void
    {
        $note = new Note(Id::new(), new NoteContent('x'), new NoteOwner('u1'), false);

        $row = (new NoteMapper())->toRow($note);

        $this->assertFalse($row['archived']);
    }
}