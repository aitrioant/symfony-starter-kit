<?php

namespace App\Tests\Functional\Infrastructure\Controller\User;

use App\Application\Command\RegisterUserCommand;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class RegisterUserControllerTest extends FunctionalTestCase
{
    public function test_register_endpoint_enqueues_command_returns_202_and_does_not_leak_password(): void
    {
        $container = static::getContainer();

        $transport = $container->get('messenger.transport.async');
        $this->assertInstanceOf(InMemoryTransport::class, $transport, '`messenger.transport.async` must be an InMemoryTransport in tests.');

        $transport->reset();

        $payload = [
            'email' => 'newuser+3@example.com',
            'password' => 'StrongPass1'
        ];

        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-Key' => 'secret'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(202, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('password_hash', $data);
        $this->assertArrayNotHasKey('passwordHash', $data);
        $responseId = $data['id'];

        $sent = $transport->getSent();
        $this->assertCount(1, $sent, 'Expected one message sent to async transport (in-memory).');

        $envelope = $sent[0];
        $message = $envelope->getMessage();
        $this->assertInstanceOf(RegisterUserCommand::class, $message);

        $this->assertSame($payload['email'], $message->email);
        $this->assertSame($payload['password'], $message->plainPassword);

        $msgId = (string)$message->id;
        $this->assertSame($responseId, $msgId);
    }

    public function test_malformed_json_returns_400(): void
    {
        // deliberately malformed JSON
        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-Key' => 'secret'],
            '{bad json'
        );

        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
    }
}
