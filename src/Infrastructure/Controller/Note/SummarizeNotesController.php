<?php

namespace App\Infrastructure\Controller\Note;

use App\Application\Query\SummarizeUserNotesQuery;
use App\Infrastructure\Messenger\Bus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/notes/summary', name: 'api_summarize_notes', methods: ['GET'])]
class SummarizeNotesController extends AbstractController
{
    public function __construct(
        private QueryBus $queryBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $ownerId = $request->query->get('ownerId');
        if ($ownerId === null || $ownerId === '') {
            return new JsonResponse(['error' => 'ownerId query parameter is required'], Response::HTTP_BAD_REQUEST);
        }

        $summary = $this->queryBus->query(new SummarizeUserNotesQuery($ownerId));

        return new JsonResponse(['summary' => $summary], Response::HTTP_OK);
    }
}