<?php

namespace App\Tests\Functional\Infrastructure\Controller\Note;

use App\Application\Command\CreateNoteCommand;
use App\Application\Handler\CreateNoteHandler;
use App\Application\Service\UserExistsChecker;
use App\Domain\ValueObject\Id;
use App\Tests\Functional\FunctionalTestCase;

class ListNotesControllerTest extends FunctionalTestCase
{
    private string $noteId;
    private string $testOwnerId;

    public function test_list_endpoint_returns_200_with_owner_notes(): void
    {
        $container = static::getContainer();

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
        $this->assertArrayHasKey('owner', $firstNote);
        $this->assertSame($this->testOwnerId, $firstNote['owner']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client->disableReboot();

        $this->testOwnerId = 'testownerid123';

        $this->replaceService(UserExistsChecker::class, new class implements UserExistsChecker {
            public function exists(string $userId): bool
            {
                return true;
            }
        });

        // generate id and create command
        $id = (string)Id::new();
        $command = new CreateNoteCommand(
            $id,
            $this->testOwnerId,
            'This is a newly created test note content.'
        );

        // call the handler directly so the note is created synchronously in the test DB
        /** @var CreateNoteHandler $handler */
        $handler = static::getContainer()->get(CreateNoteHandler::class);
        $handler($command);

        $this->noteId = $id;
    }
}
