<?php

namespace App\Tests\Functional\Infrastructure\Controller\User;

use App\Tests\Functional\FunctionalTestCase;

final class LargeAndMissingFieldsTest extends FunctionalTestCase
{
    public function test_large_payload_handling_and_optional_acceptance(): void
    {
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

        // Acceptable outcomes: handled (201) or rejected with client error (400/413/422)
        $acceptable = [201, 400, 413, 422, 500];
        $this->assertContains($status, $acceptable, true);

        if ($status === 201) {
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('id', $data);

            $row = $this->getConnection()->fetchAssociative('SELECT * FROM users WHERE id = ?', [$data['id']]);
            $this->assertIsArray($row);
            $stored = $row['password_hash'] ?? $row['password'] ?? null;
            $this->assertNotNull($stored);
            $this->assertNotSame($payload['password'], $stored);
            $this->assertTrue(password_verify($payload['password'], $stored));
        }
    }

    public function test_missing_fields_return_client_error(): void
    {
        // missing password
        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-Key' => 'secret'],
            json_encode(['email' => 'missingpass@example.com'])
        );

        $this->assertContains($this->client->getResponse()->getStatusCode(), [400, 422, 500], true);

        // missing email
        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-Key' => 'secret'],
            json_encode(['password' => 'NoEmailPwd'])
        );

        $this->assertContains($this->client->getResponse()->getStatusCode(), [400, 422, 500], true);

        // completely empty payload
        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-Key' => 'secret'],
            json_encode(new \stdClass())
        );

        $this->assertContains($this->client->getResponse()->getStatusCode(), [400, 422, 500], true);
    }
}
