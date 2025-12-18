<?php

namespace App\Tests\Unit\Infrastructure\Controller\User;

use App\Application\Command\RegisterUserCommand;
use App\Domain\ValueObject\Id;
use App\Infrastructure\Controller\User\RegisterUserController;
use App\Tests\Helper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class RegisterUserControllerTest extends TestCase
{
    public function test_invoke_calls_register_user_and_returns_201(): void
    {
        $email = 'user@example.com';
        $password = 'secret';

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(RegisterUserCommand::class))
            ->willReturn(new Envelope(new RegisterUserCommand(Id::new(), $email, $password)));

        $controller = new RegisterUserController($bus);

        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        $response = $controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(202, $response->getStatusCode());

        $data = json_decode($response->getContent() ?: '{}', true);
        $this->assertArrayHasKey('id', $data);
        $this->assertTrue((Helper::uuidV4Matcher())($data['id']));
    }

    public function test_invoke_with_missing_fields_uses_empty_strings(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(RegisterUserCommand::class))
            ->willReturn(new Envelope(new RegisterUserCommand(Id::new(), '', '')));

        $controller = new RegisterUserController($bus);

        $request = new Request([], [], [], [], [], [], '{}');

        $response = $controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(202, $response->getStatusCode());

        $data = json_decode($response->getContent() ?: '{}', true);
        $this->assertArrayHasKey('id', $data);
        $this->assertTrue((Helper::uuidV4Matcher())($data['id']));
    }
}
