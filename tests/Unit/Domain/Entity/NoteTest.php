<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Note;
use App\Domain\Exception\NotOwnerException;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteContent;
use App\Domain\ValueObject\NoteOwner;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class NoteTest extends TestCase
{
    public function testConstructorPreservesTimestamps(): void
    {
        $id = Id::new();
        $created = new DateTimeImmutable('2020-01-01 00:00:00');
        $updated = new DateTimeImmutable('2020-01-02 00:00:00');

        $note = new Note($id, new NoteContent('x'), new NoteOwner('owner'), false, $created, $updated);

        $this->assertSame($created, $note->createdAt());
        $this->assertSame($updated, $note->updatedAt());
    }

    public function testUpdateContentByNonOwnerThrows(): void
    {
        $id = Id::new();
        $note = new Note($id, new NoteContent('initial'), new NoteOwner('owner'));
        $this->expectException(NotOwnerException::class);
        $note->updateContent(new NoteContent('changed'), new NoteOwner('someone-else'));
    }

    public function testUpdateContentUpdatesAndSetsUpdatedAt(): void
    {
        $id = Id::new();
        $owner = new NoteOwner('owner-1');
        $note = new Note($id, new NoteContent('initial'), $owner);

        $before = $note->updatedAt();
        usleep(1000);
        $note->updateContent(new NoteContent('new'), $owner);

        $this->assertSame('new', (string)$note->content());
        $this->assertGreaterThan($before, $note->updatedAt());
    }

    public function testArchiveByOwnerSetsArchivedAndUpdatedAt(): void
    {
        $id = Id::new();
        $owner = new NoteOwner('owner-2');
        $note = new Note($id, new NoteContent('text'), $owner);

        $this->assertFalse($note->isArchived());
        $before = $note->updatedAt();
        usleep(1000);
        $note->archive($owner);

        $this->assertTrue($note->isArchived());
        $this->assertGreaterThan($before, $note->updatedAt());
    }

    public function testToArrayContainsExpectedKeys(): void
    {
        $id = Id::new();
        $created = new DateTimeImmutable('2021-01-01 00:00:00');
        $updated = new DateTimeImmutable('2021-01-02 00:00:00');
        $note = new Note($id, new NoteContent('content'), new NoteOwner('owner-3'), false, $created, $updated);

        $arr = $note->toArray();

        $this->assertSame((string)$id, $arr['id']);
        $this->assertSame('content', $arr['content']);
        $this->assertSame('owner-3', $arr['owner']);
        $this->assertSame($created->format(DATE_ATOM), $arr['createdAt']);
        $this->assertSame($updated->format(DATE_ATOM), $arr['updatedAt']);
    }
}
