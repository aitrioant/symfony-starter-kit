<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Doctrine\Mapper;

use App\Domain\ValueObject\Id;
use App\Infrastructure\Doctrine\Mapper\NoteMapper;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class NoteMapperTest extends TestCase
{
    public function testToDomainAndToRowPreserveTimestampsAndFields(): void
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

        $mapper = new NoteMapper();
        $note = $mapper->toDomain($row);

        $this->assertSame($id, (string)$note->id());
        $this->assertSame('hello', (string)$note->content());
        $this->assertSame('u1', (string)$note->owner());
        $this->assertTrue($note->isArchived());
        $this->assertSame($created->format('Y-m-d H:i:s'), $note->createdAt()->format('Y-m-d H:i:s'));
        $this->assertSame($updated->format('Y-m-d H:i:s'), $note->updatedAt()->format('Y-m-d H:i:s'));

        $rowOut = $mapper->toRow($note);

        $this->assertSame($id, $rowOut['id']);
        $this->assertSame('hello', $rowOut['content']);
        $this->assertSame('u1', $rowOut['owner_id']);
        $this->assertTrue($rowOut['archived']);
        $this->assertSame($created->format('Y-m-d H:i:s'), $rowOut['created_at']);
        $this->assertSame($updated->format('Y-m-d H:i:s'), $rowOut['updated_at']);
    }
}
