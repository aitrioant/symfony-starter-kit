<?php
// php
namespace App\Tests\Functional;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTestCase extends WebTestCase
{
    protected ?KernelBrowser $client = null;
    protected ?Connection $connection = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->connection = $this->client->getContainer()->get('doctrine')->getConnection();
        $this->connection->beginTransaction();
    }

    protected function getConnection(): Connection
    {
        if ($this->connection === null) {
            throw new \LogicException('Connection not initialized.');
        }
        return $this->connection;
    }

    protected function tearDown(): void
    {
        if ($this->connection !== null && $this->connection->isTransactionActive()) {
            $this->connection->rollBack();
            $this->connection->close();
        }
        parent::tearDown();
    }

    protected function requestJson(string $method, string $uri, array $data = [], array $headers = []): void
    {
        $server = array_merge(['CONTENT_TYPE' => 'application/json'], $headers);
        $this->client->request($method, $uri, [], [], $server, json_encode($data));
    }

    protected function replaceService(string $id, $service): void
    {
        $this->client->getContainer()->set($id, $service);
    }
}
