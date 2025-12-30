<?php

namespace App\Infrastructure\Messenger\Bus;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class QueryBus
{
    use HandleTrait;

    public function __construct(
        #[Autowire('@query.bus')]
        private MessageBusInterface $messageBus,
    )
    {
    }

    /**
     * @param object|Envelope $query
     *
     * @return mixed The handler returned value
     */
    public function query($query): mixed
    {
        return $this->handle($query);
    }
}
