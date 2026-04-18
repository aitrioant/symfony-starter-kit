<?php

namespace App\Infrastructure\Controller\User;

use App\Application\Command\RegisterUserCommand;
use App\Domain\ValueObject\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users', name: 'api_register_user', methods: ['POST'])]
final class RegisterUserController extends AbstractController
{

    public function __construct(
        private readonly MessageBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '{}', true);
        if (!is_array($data) || empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        $id = Id::new();
        $command = new RegisterUserCommand($id, $data['email'], $data['password']);
        $this->commandBus->dispatch($command);

        return new JsonResponse(['id' => (string)$id], Response::HTTP_ACCEPTED);
    }
}
