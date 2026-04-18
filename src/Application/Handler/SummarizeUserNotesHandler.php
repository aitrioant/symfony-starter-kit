<?php

namespace App\Application\Handler;

use App\Application\Query\SummarizeUserNotesQuery;
use App\Domain\Entity\Note;
use App\Domain\Repository\NoteRepositoryInterface;
use App\Domain\Service\AiSummaryService;
use App\Domain\ValueObject\NoteOwner;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class SummarizeUserNotesHandler
{
    public function __construct(
        private NoteRepositoryInterface $repository,
        private AiSummaryService        $summaryService,
    )
    {
    }

    public function __invoke(SummarizeUserNotesQuery $query): string
    {
        $owner = new NoteOwner($query->ownerId);
        $active = array_values(array_filter(
            $this->repository->findByOwner((string)$owner),
            static fn(Note $n) => !$n->isArchived(),
        ));

        if ($active === []) {
            return '';
        }

        return $this->summaryService->summarize($active);
    }
}