<?php

namespace App\Application\Command;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final readonly class UpdateNoteCommand
{
    public function __construct(
        public string $id,
        public string $ownerId,
        public string $content
    )
    {
    }
}
