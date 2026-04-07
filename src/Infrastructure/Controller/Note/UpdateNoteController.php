<?php

namespace App\Infrastructure\Controller\Note;

use App\Application\Command\UpdateNoteCommand;
use App\Domain\Exception\NoteNotFound;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/notes/{id}', name: 'api_update_note', methods: ['PUT', 'PATCH'])]
class UpdateNoteController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '', true);
        if (!is_array($data) || empty($data['ownerId']) || empty($data['content'])) {
            return new JsonResponse(['error' => 'ownerId and content are required'], Response::HTTP_BAD_REQUEST);
        }

        $command = new UpdateNoteCommand($id, $data['ownerId'], $data['content']);
        try {
            $this->commandBus->dispatch($command);
        } catch (NoteNotFound $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
