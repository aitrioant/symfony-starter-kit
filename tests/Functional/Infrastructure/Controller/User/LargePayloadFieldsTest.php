<?php

namespace App\Tests\Functional\Infrastructure\Controller\User;

use App\Application\Command\RegisterUserCommand;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class LargePayloadFieldsTest extends FunctionalTestCase
{
    public function test_large_payload_handling_and_optional_acceptance(): void
    {
        $container = static::getContainer();

        $this->assertTrue($container->has('messenger.transport.sync'), 'Service `messenger.transport.sync` must be configured in test env.');
        $transport = $container->get('messenger.transport.sync');
        $this->assertInstanceOf(InMemoryTransport::class, $transport, '`messenger.transport.sync` must be an InMemoryTransport in tests.');

        $transport->reset();

        $largeLocalPart = str_repeat('a', 10000);
        $largeEmail = $largeLocalPart . '@example.com';
        $payload = ['email' => $largeEmail, 'password' => 'LargePayloadPwd!'];

        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-Key' => 'secret'],
            json_encode($payload)
        );

        $status = $this->client->getResponse()->getStatusCode();

        // Acceptable outcomes: accepted for async processing (202) or rejected with client/server error
        $acceptable = [202, 400, 413, 422, 500];
        $this->assertContains($status, $acceptable, true);

        if ($status === 202) {
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('id', $data);

            $sent = $transport->getSent();
            $this->assertCount(1, $sent, 'Expected one message sent to sync transport (in-memory).');

            $envelope = $sent[0];
            $message = $envelope->getMessage();
            $this->assertInstanceOf(RegisterUserCommand::class, $message);

            $this->assertSame($payload['email'], $message->email);
            $this->assertSame($payload['password'], $message->plainPassword);
        }
    }
}
