<?php

namespace App\Infrastructure\Controller\User;

use App\Application\Command\RegisterUserCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/users', name: 'api_register_user', methods: ['POST'])]
final class RegisterUserController extends AbstractController
{

    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '{}', true);
        $id = Uuid::v4()->toRfc4122();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $command = new RegisterUserCommand($id, $email, $password);
        $this->messageBus->dispatch($command);

        return new JsonResponse(['id' => $id], 201);
    }
}
