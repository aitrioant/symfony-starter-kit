<?php

namespace App\Tests\Integration\Infrastructure\Messenger\Transport;

use App\Application\Command\RegisterUserCommand;
use App\Domain\ValueObject\Id;
use App\Tests\Helper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

final class RegisterUserMessageTransportTest extends KernelTestCase
{
    public function test_sync_transport_is_inmemory_and_receives_register_user_command(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->assertTrue($container->has('messenger.transport.sync'), 'Service `messenger.transport.sync` must be configured in test env.');

        /** @var InMemoryTransport $transport */
        $transport = $container->get('messenger.transport.sync');

        // the test environment should inject InMemoryTransport for `sync`
        $this->assertInstanceOf(InMemoryTransport::class, $transport, '`messenger.transport.sync` must be an InMemoryTransport in tests.');

        $transport->reset();

        /** @var MessageBusInterface $bus */
        $bus = $container->get(MessageBusInterface::class);

        $email = 'integration@example.com';
        $password = 's3cret';
        $id = Id::new();

        $bus->dispatch(new RegisterUserCommand($id, $email, $password));

        $sent = $transport->getSent();
        $this->assertCount(1, $sent, 'Expected one message sent to sync transport (in-memory).');

        $envelope = $sent[0];
        $message = $envelope->getMessage();
        $this->assertInstanceOf(RegisterUserCommand::class, $message);

        $this->assertEquals($email, $message->email);

        $this->assertEquals($password, $message->plainPassword);

        $idValue = (string)$message->id;

        $this->assertTrue((Helper::uuidV4Matcher())($idValue));
    }
}
