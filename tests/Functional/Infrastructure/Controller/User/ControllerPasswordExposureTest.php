<?php

namespace App\Tests\Functional\Infrastructure\Controller\User;

use App\Application\Command\RegisterUserCommand;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Helper;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class ControllerPasswordExposureTest extends FunctionalTestCase
{
    public function test_controller_response_does_not_expose_password_and_dispatches_command(): void
    {
        $container = static::getContainer();

        $transport = $container->get('messenger.transport.sync');
        $this->assertInstanceOf(InMemoryTransport::class, $transport, '`messenger.transport.sync` must be an InMemoryTransport in tests.');

        $transport->reset();

        $payload = [
            'email' => 'noexpose+1@example.com',
            'password' => 'TopSecret123'
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
        $this->assertIsArray($data);

        // Ensure response does not contain password fields
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('password_hash', $data);
        $this->assertArrayNotHasKey('passwordHash', $data);

        $this->assertArrayHasKey('id', $data);
        $responseId = $data['id'];

        $sent = $transport->getSent();
        $this->assertCount(1, $sent, 'Expected one message sent to sync transport (in-memory).');

        $envelope = $sent[0];
        $message = $envelope->getMessage();
        $this->assertInstanceOf(RegisterUserCommand::class, $message);
        $this->assertEquals($payload['email'], $message->email);

        $this->assertEquals($payload['password'], $message->plainPassword);

        $msgId = (string)$message->id;
        $this->assertSame($responseId, $msgId);
        $this->assertTrue((Helper::uuidV4Matcher())($msgId));
    }
}
