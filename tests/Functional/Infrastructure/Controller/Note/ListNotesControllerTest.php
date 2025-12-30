<?php

namespace App\Tests\Functional\Infrastructure\Controller\Note;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class ListNotesControllerTest extends FunctionalTestCase
{
    private string $noteId;
    private string $testOwnerId;

    public function test_successful_note_listing_returns_202(): void
    {
        $container = static::getContainer();

        $transport = $container->get('messenger.transport.sync');
        $this->assertInstanceOf(InMemoryTransport::class, $transport, '`messenger.transport.sync` must be an InMemoryTransport in tests.');

        $transport->reset();

        $this->client->request(
            'GET',
            '/api/notes',
            ['ownerId' => $this->testOwnerId],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-Key' => 'secret']
        );

        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $firstNote = $data[0] ?? null;
        $this->assertIsArray($firstNote);
        $this->assertArrayHasKey('id', $firstNote);
        $this->assertSame($this->noteId, $firstNote['id']);
        $this->assertArrayHasKey('ownerId', $firstNote);
        $this->assertSame($this->testOwnerId, $firstNote['ownerId']);
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
