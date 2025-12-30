<?php

namespace App\Tests\Functional\Infrastructure\Controller\Note;

use App\Application\Command\UpdateNoteCommand;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class UpdateNoteControllerTest extends FunctionalTestCase
{
    private string $noteId;
    private string $testOwnerId;

    public function test_successful_note_update_returns_202_and_dispatches_command_to_inmemory_transport(): void
    {
        $container = static::getContainer();

        $transport = $container->get('messenger.transport.sync');
        $this->assertInstanceOf(InMemoryTransport::class, $transport, '`messenger.transport.sync` must be an InMemoryTransport in tests.');

        $transport->reset();

        $payload = [
            'ownerId' => $this->testOwnerId,
            'content' => 'This is an updated test note content.',
        ];

        $this->client->request(
            'PATCH',
            '/api/notes/' . $this->noteId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-Key' => 'secret'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(202, $response->getStatusCode());

        $sent = $transport->getSent();
        $this->assertCount(1, $sent, 'Expected one message sent to sync transport (in-memory).');

        $envelope = $sent[0];
        $message = $envelope->getMessage();
        $this->assertInstanceOf(UpdateNoteCommand::class, $message);

        $this->assertSame($payload['ownerId'], $message->ownerId);
        $this->assertSame($payload['content'], $message->content);

        $msgId = (string)$message->id;
        $this->assertSame($this->noteId, $msgId);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client->disableReboot();

        $this->testOwnerId = 'testownerid123';
        $payload = [
            'ownerId' => $this->testOwnerId,
            'content' => 'This is a newly created test note content.',
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
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);

        $this->noteId = $data['id'];
    }
}
