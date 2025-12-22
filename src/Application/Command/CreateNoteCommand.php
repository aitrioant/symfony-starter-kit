<?php

namespace App\Application\Command;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
final readonly class CreateNoteCommand
{
    public function __construct(
        public string $id,
        public string $ownerId,
        public string $content
    )
    {
    }
}
