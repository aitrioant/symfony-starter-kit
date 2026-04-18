<?php

namespace App\Tests\Unit\Application\Handler;

use App\Application\Handler\SummarizeUserNotesHandler;
use App\Application\Query\SummarizeUserNotesQuery;
use App\Domain\Entity\Note;
use App\Domain\Repository\NoteRepositoryInterface;
use App\Domain\Service\AiSummaryService;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteContent;
use App\Domain\ValueObject\NoteOwner;
use PHPUnit\Framework\TestCase;

final class SummarizeUserNotesHandlerTest extends TestCase
{
    public function test_summarizes_only_active_notes(): void
    {
        $owner = new NoteOwner('11111111-1111-4111-8111-111111111111');
        $active = new Note(Id::new(), new NoteContent('keep me'), $owner);
        $archived = new Note(Id::new(), new NoteContent('ignore me'), $owner, archived: true);

        $repository = $this->createMock(NoteRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findByOwner')
            ->with((string)$owner)
            ->willReturn([$active, $archived]);

        $service = $this->createMock(AiSummaryService::class);
        $service->expects($this->once())
            ->method('summarize')
            ->with($this->callback(static fn(array $notes) => count($notes) === 1 && $notes[0] === $active))
            ->willReturn('a summary');

        $handler = new SummarizeUserNotesHandler($repository, $service);
        $result = $handler(new SummarizeUserNotesQuery((string)$owner));

        self::assertSame('a summary', $result);
    }

    public function test_returns_empty_string_when_no_active_notes(): void
    {
        $owner = new NoteOwner('22222222-2222-4222-8222-222222222222');
        $archived = new Note(Id::new(), new NoteContent('ignore'), $owner, archived: true);

        $repository = $this->createStub(NoteRepositoryInterface::class);
        $repository->method('findByOwner')->willReturn([$archived]);

        $service = $this->createMock(AiSummaryService::class);
        $service->expects($this->never())->method('summarize');

        $handler = new SummarizeUserNotesHandler($repository, $service);
        $result = $handler(new SummarizeUserNotesQuery((string)$owner));

        self::assertSame('', $result);
    }
}