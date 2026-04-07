<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Doctrine;

use App\Domain\Entity\Note;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteContent;
use App\Domain\ValueObject\NoteOwner;
use App\Infrastructure\Doctrine\DoctrineNoteRepository;
use App\Infrastructure\Doctrine\Domain\Entity\OrmNote;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

final class DoctrineNoteRepositoryTest extends TestCase
{
    public function testSaveCreatesOrmWhenNotFoundAndPersists(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repoMock = $this->createMock(EntityRepository::class);
        $id = Id::new();
        $repoMock->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);

        $em->expects($this->once())
            ->method('getRepository')
            ->with(OrmNote::class)
            ->willReturn($repoMock);

        $captured = null;
        $em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($arg) use (&$captured) {
                $captured = $arg;
            });

        $em->expects($this->once())->method('flush');

        $repository = new DoctrineNoteRepository($em);
        $note = new Note($id, new NoteContent('abc'), new NoteOwner('owner-z'));

        $repository->save($note);

        $this->assertInstanceOf(OrmNote::class, $captured);
        $this->assertSame((string)$id, $captured->getId());
        $this->assertSame('abc', $captured->getContent());
        $this->assertSame('owner-z', $captured->getOwnerId());
    }

    public function testFindByIdReturnsDomainNotePreservingTimestampsAndArchivedState(): void
    {
        $id = (string)Id::new();
        $orm = new OrmNote($id, 'c1', 'owner-1');
        $orm->setArchived(true);

        $createdAt = $orm->getCreatedAt();
        $updatedAt = $orm->getUpdatedAt();

        $repoMock = $this->createMock(EntityRepository::class);
        $repoMock->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($orm);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(OrmNote::class)
            ->willReturn($repoMock);

        $repository = new DoctrineNoteRepository($em);

        $domain = $repository->findById($id);

        $this->assertNotNull($domain);
        $this->assertTrue($domain->isArchived());
        $this->assertSame('c1', (string)$domain->content());
        // created/updated preservation expected by correct implementation
        $this->assertSame($createdAt->format('Y-m-d H:i:s'), $domain->createdAt()->format('Y-m-d H:i:s'));
        $this->assertSame($updatedAt->format('Y-m-d H:i:s'), $domain->updatedAt()->format('Y-m-d H:i:s'));
    }
}
