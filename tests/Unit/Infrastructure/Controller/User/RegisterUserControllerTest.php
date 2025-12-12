<?php

namespace App\Tests\Unit\Infrastructure\Controller\User;

use App\Application\Handler\RegisterUser;
use App\Infrastructure\Controller\User\RegisterUserController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class RegisterUserControllerTest extends TestCase
{
    public function test_invoke_calls_register_user_and_returns_201(): void
    {
        $email = 'user@example.com';
        $password = 'secret';

        $registerUser = $this->createMock(RegisterUser::class);
        $registerUser->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback($this->uuidV4Matcher()),
                $this->equalTo($email),
                $this->equalTo($password)
            );

        $controller = new RegisterUserController();

        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        $response = $controller->__invoke($request, $registerUser);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());

        $data = json_decode($response->getContent() ?: '{}', true);
        $this->assertArrayHasKey('id', $data);
        $this->assertTrue(($this->uuidV4Matcher())($data['id']));
    }

    private function uuidV4Matcher(): callable
    {
        return function ($id) {
            // RFC4122 v4 like: 8-4-4-4-12 hex chars
            return is_string($id) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id) === 1;
        };
    }

    public function test_invoke_with_missing_fields_uses_empty_strings(): void
    {
        $registerUser = $this->createMock(RegisterUser::class);
        $registerUser->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->callback($this->uuidV4Matcher()),
                $this->equalTo(''),
                $this->equalTo('')
            );

        $controller = new RegisterUserController();

        $request = new Request([], [], [], [], [], [], '{}');

        $response = $controller->__invoke($request, $registerUser);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());

        $data = json_decode($response->getContent() ?: '{}', true);
        $this->assertArrayHasKey('id', $data);
        $this->assertTrue(($this->uuidV4Matcher())($data['id']));
    }
}
