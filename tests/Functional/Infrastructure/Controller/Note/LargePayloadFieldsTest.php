<?php

namespace App\Tests\Functional\Infrastructure\Controller\Note;

use App\Domain\ValueObject\NoteContent;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class LargePayloadFieldsTest extends FunctionalTestCase
{
    public function test_content_exceeding_note_content_max_length_returns_422_and_does_not_dispatch(): void
    {
        $container = static::getContainer();

        $transport = $container->get('messenger.transport.async');
        $this->assertInstanceOf(InMemoryTransport::class, $transport, '`messenger.transport.async` must be an InMemoryTransport in tests.');
        $transport->reset();

        $payload = [
            'ownerId' => 'testownerid123',
            'content' => str_repeat('a', NoteContent::MAX_LENGTH + 1),
        ];

        $this->client->request(
            'POST',
            '/api/notes',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-Key' => 'secret'],
            json_encode($payload)
        );

        $this->assertSame(422, $this->client->getResponse()->getStatusCode());
        $this->assertCount(0, $transport->getSent(), 'Expected no command dispatched for oversized content.');
    }
}
