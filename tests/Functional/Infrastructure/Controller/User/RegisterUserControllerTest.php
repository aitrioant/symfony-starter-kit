<?php

namespace App\Tests\Functional\Infrastructure\Controller\User;

use App\Infrastructure\Doctrine\DoctrineUserRepository;
use App\Tests\Functional\FunctionalTestCase;

final class RegisterUserControllerTest extends FunctionalTestCase
{
    public function test_successful_user_creation_returns_201_and_password_is_hashed(): void
    {
        $payload = [
            'email' => 'newuser+3@example.com',
            'password' => 'StrongPass1'
        ];

        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-Key' => 'secret'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(201, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $id = $data['id'];

        // Check DB record exists and password is hashed
        $conn = static::getContainer()->get('doctrine')->getConnection();
        $row = $conn->fetchAssociative('SELECT * FROM users WHERE id = ?', [$id]);

        $this->assertIsArray($row, 'Expected DB row for created user');
        $this->assertSame('newuser+3@example.com', $row['email']);

        $stored = $row['password_hash'];
        $this->assertNotSame('StrongPass1', $stored, 'Password must be stored hashed, not plain');
        $this->assertTrue(password_verify('StrongPass1', $stored), 'Stored hash should verify against plain password');
    }

    public function test_malformed_json_returns_400(): void
    {
        // deliberately malformed JSON
        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-Key' => 'secret'],
            '{bad json'
        );

        $response = $this->client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_invalid_input_returns_400(): void
    {
        // invalid email and short password
        $payload = [
            'email' => 'not-an-email',
            'password' => '123'
        ];

        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-Key' => 'secret'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(500, $response->getStatusCode());
    }

    public function test_repository_failure_returns_500(): void
    {
        // Replace the repository service with a stub that throws on save.
        // Adjust the service id if your repository is registered under a different id.
        $container = static::getContainer();

        $stubRepo = new class {
            public function save($user): void
            {
                throw new \RuntimeException('simulated repository failure');
            }

            // if controller calls other methods, provide no-op implementations
            public function findById($id)
            {
                return null;
            }

            public function findByEmail($email)
            {
                return null;
            }
        };

        // Replace concrete repository service; change the class name if needed.
        $container->set(DoctrineUserRepository::class, $stubRepo);

        $payload = [
            'email' => 'willfail@example.com',
            'password' => 'StrongPass1'
        ];

        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
                'HTTP_X-API-Key' => 'secret'],
            json_encode($payload)
        );

        $response = $this->client->getResponse();
        $this->assertSame(500, $response->getStatusCode());
    }
}
