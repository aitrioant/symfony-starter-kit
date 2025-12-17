<?php

namespace App\Application\Command;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
class RegisterUserCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $plainPassword,
    )
    {
    }
}
