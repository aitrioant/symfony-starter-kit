<?php

namespace App\Tests\Functional\Infrastructure\Controller\User;

use App\Tests\Functional\FunctionalTestCase;

final class ControllerPasswordExposureTest extends FunctionalTestCase
{
    public function test_controller_response_does_not_expose_password_and_db_stores_hashed(): void
    {
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
        $id = $data['id'];

        // Check DB record and stored hash
        $row = $this->connection->fetchAssociative('SELECT * FROM users WHERE id = ?', [$id]);
        $this->assertIsArray($row, 'Expected DB row for created user');

        $stored = $row['password_hash'] ?? $row['password'] ?? null;
        $this->assertNotNull($stored, 'Expected a password hash column in DB');

        // stored must not equal raw and must verify
        $this->assertNotSame('TopSecret123', $stored, 'DB must not store raw password');
        $this->assertTrue(password_verify('TopSecret123', $stored), 'Stored hash should verify against plain password');
    }
}
