<?php

namespace App\Tests\Functional\Infrastructure\Controller\Note;

use App\Application\Command\CreateNoteCommand;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class CreateNoteControllerTest extends FunctionalTestCase
{
    public function test_successful_note_creation_returns_202_and_dispatches_command_to_inmemory_transport(): void
    {
        $container = static::getContainer();

        $transport = $container->get('messenger.transport.sync');
        $this->assertInstanceOf(InMemoryTransport::class, $transport, '`messenger.transport.sync` must be an InMemoryTransport in tests.');

        $transport->reset();

        $payload = [
            'ownerId' => 'testownerid123',
            'content' => 'This is a test note content.',
        ];

        $this->client->request(
            'POST',
            '/api/notes',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-Key' => 'secret'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(202, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $responseId = $data['id'];

        $sent = $transport->getSent();
        $this->assertCount(1, $sent, 'Expected one message sent to sync transport (in-memory).');

        $envelope = $sent[0];
        $message = $envelope->getMessage();
        $this->assertInstanceOf(CreateNoteCommand::class, $message);

        $this->assertSame($payload['ownerId'], $message->ownerId);
        $this->assertSame($payload['content'], $message->content);

        $msgId = (string)$message->id;
        $this->assertSame($responseId, $msgId);
    }
}
