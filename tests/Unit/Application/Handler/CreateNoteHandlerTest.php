<?php

namespace App\Tests\Unit\Application\Handler;

use App\Application\Command\CreateNoteCommand;
use App\Application\Handler\CreateNoteHandler;
use App\Application\Service\UserExistsChecker;
use App\Domain\Entity\Note;
use App\Domain\Exception\OwnerNotFound;
use App\Domain\Repository\NoteRepositoryInterface;
use App\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class CreateNoteHandlerTest extends TestCase
{
    public function test_saves_note_when_owner_exists(): void
    {
        $id = Id::new();
        $ownerId = '11111111-1111-4111-8111-111111111111';

        $userExists = $this->createMock(UserExistsChecker::class);
        $userExists->expects($this->once())
            ->method('exists')
            ->with($ownerId)
            ->willReturn(true);

        $repository = $this->createMock(NoteRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Note::class));

        $handler = new CreateNoteHandler($repository, $userExists);
        $handler(new CreateNoteCommand($id, $ownerId, 'some content'));
    }

    public function test_throws_owner_not_found_when_user_does_not_exist(): void
    {
        $ownerId = 'ghost-user';

        $userExists = $this->createStub(UserExistsChecker::class);
        $userExists->method('exists')->willReturn(false);

        $repository = $this->createMock(NoteRepositoryInterface::class);
        $repository->expects($this->never())->method('save');

        $this->expectException(OwnerNotFound::class);

        $handler = new CreateNoteHandler($repository, $userExists);
        $handler(new CreateNoteCommand(Id::new(), $ownerId, 'some content'));
    }
}