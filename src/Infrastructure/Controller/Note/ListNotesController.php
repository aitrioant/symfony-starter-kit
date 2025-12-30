<?php

namespace App\Infrastructure\Controller\Note;

use App\Application\Query\ListNotesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/notes', name: 'api_list_notes', methods: ['GET'])]
class ListNotesController extends AbstractController
{
    public function __construct(
        #[Autowire('@query.bus')]
        private MessageBusInterface $queryBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $ownerId = $request->query->get('ownerId');
        if ($ownerId === null) {
            return new JsonResponse(['error' => 'ownerId query parameter is required'], Response::HTTP_BAD_REQUEST);
        }

        $query = new ListNotesQuery($ownerId);
        $notes = $this->queryBus->dispatch($query);

        return new JsonResponse($notes, Response::HTTP_OK);
    }
}
