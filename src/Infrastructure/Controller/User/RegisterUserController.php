<?php

namespace App\Infrastructure\Controller\User;

use App\Application\Handler\RegisterUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/users', name: 'api_register_user', methods: ['POST'])]
final class RegisterUserController extends AbstractController
{
    public function __invoke(Request $request, RegisterUser $registerUser): JsonResponse
    {
        $raw = $request->getContent() ?: '{}';
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Malformed JSON payload'], 400);
        }

        //$data = json_decode($request->getContent() ?: '{}', true);
        $id = Uuid::v4()->toRfc4122();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $registerUser($id, $email, $password);

        return new JsonResponse(['id' => $id], 201);
    }
}
