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
use PDOStatement;
use PHPUnit\Framework\TestCase;

final class PDONoteRepositoryTest extends TestCase
{
    public function testSavePerformsInsertWhenNotExists(): void
    {
        $pdo = $this->createMock(PDO::class);
        $selectStmt = $this->createMock(PDOStatement::class);
        $insertStmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnCallback(function (string $sql) use ($selectStmt, $insertStmt) {
                if (strpos($sql, 'SELECT COUNT(1) FROM notes WHERE id = :id') !== false) {
                    return $selectStmt;
                }
                if (strpos($sql, 'INSERT INTO notes') !== false) {
                    return $insertStmt;
                }
                $this->fail('Unexpected SQL passed to prepare: ' . $sql);
            });

        $selectStmt->expects($this->once())
            ->method('execute')
            ->with($this->arrayHasKey(':id'));
        $selectStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('0');

        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params) {
                // required keys
                return isset($params[':id'], $params[':content'], $params[':owner_id'], $params[':archived'], $params[':created_at'], $params[':updated_at']);
            }));

        $mapper = new NoteMapper();
        $repo = new PDONoteRepository($pdo, $mapper);

        $created = new DateTimeImmutable('2023-01-01 10:00:00');
        $note = new Note(Id::new(), new NoteContent('abc'), new NoteOwner('owner-x'), false, $created, $created);

        $repo->save($note);
    }

    public function testSavePerformsUpdateWhenExists(): void
    {
        $pdo = $this->createMock(PDO::class);
        $selectStmt = $this->createMock(PDOStatement::class);
        $updateStmt = $this->createMock(PDOStatement::class);

        $pdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnCallback(function (string $sql) use ($selectStmt, $updateStmt) {
                if (strpos($sql, 'SELECT COUNT(1) FROM notes WHERE id = :id') !== false) {
                    return $selectStmt;
                }
                if (strpos($sql, 'UPDATE notes SET') !== false) {
                    return $updateStmt;
                }
                $this->fail('Unexpected SQL passed to prepare: ' . $sql);
            });

        $selectStmt->expects($this->once())
            ->method('execute')
            ->with($this->arrayHasKey(':id'));
        $selectStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('1');

        $updateStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function (array $params) {
                return isset($params[':id'], $params[':content'], $params[':owner_id'], $params[':archived'], $params[':updated_at']);
            }));

        $mapper = new NoteMapper();
        $repo = new PDONoteRepository($pdo, $mapper);

        $note = new Note(Id::new(), new NoteContent('updated'), new NoteOwner('owner-y'));

        $repo->save($note);
    }
}
