<?php

namespace App\Infrastructure\Controller\User;

use App\Application\Command\RegisterUserCommand;
use App\Domain\ValueObject\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users', name: 'api_register_user', methods: ['POST'])]
final class RegisterUserController extends AbstractController
{

    public function __construct(
        #[Autowire('messenger.bus.sync')]
        private readonly MessageBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '{}', true);
        $id = Id::new();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $command = new RegisterUserCommand($id, $email, $password);
        $this->commandBus->dispatch($command);

        return new JsonResponse(['id' => (string)$id], 202);
    }
}
