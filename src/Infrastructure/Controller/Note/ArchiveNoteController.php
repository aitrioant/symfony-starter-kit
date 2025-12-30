<?php

namespace App\Infrastructure\Controller\Note;

use App\Application\Command\ArchiveNoteCommand;
use App\Domain\Exception\NoteNotFound;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/notes/{id}/archive', name: 'api_archive_note', methods: ['POST'])]
class ArchiveNoteController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '', true);
        if (!is_array($data) || empty($data['ownerId'])) {
            return new JsonResponse(['error' => 'ownerId is required'], Response::HTTP_BAD_REQUEST);
        }

        $command = new ArchiveNoteCommand($id, $data['ownerId']);
        try {
            $this->commandBus->dispatch($command);
        } catch (NoteNotFound $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
