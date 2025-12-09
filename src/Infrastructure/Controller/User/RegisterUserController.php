<?php

namespace App\Infrastructure\Controller\User;

use App\Application\Command\RegisterUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

final class RegisterUserController extends AbstractController
{
    public function __invoke(Request $request, RegisterUser $registerUser): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '{}', true);
        $id = Uuid::v4()->toRfc4122();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $registerUser($id, $email, $password);

        return new JsonResponse(['id' => $id], 201);
    }
}
