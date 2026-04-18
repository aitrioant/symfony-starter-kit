<?php

namespace App\Infrastructure\Controller\Note;

use App\Application\Command\CreateNoteCommand;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteContent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/notes', name: 'api_create_note', methods: ['POST'])]
class CreateNoteController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '', true);
        if (!is_array($data) || empty($data['ownerId']) || empty($data['content'])) {
            return new JsonResponse(['error' => 'ownerId and content are required'], Response::HTTP_BAD_REQUEST);
        }

        if (mb_strlen($data['content']) > NoteContent::MAX_LENGTH) {
            return new JsonResponse(
                ['error' => sprintf('content exceeds %d characters', NoteContent::MAX_LENGTH)],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $id = Id::new();
        $command = new CreateNoteCommand($id, $data['ownerId'], $data['content']);

        $this->commandBus->dispatch($command);

        return new JsonResponse(['id' => (string)$id], Response::HTTP_ACCEPTED);
    }
}
