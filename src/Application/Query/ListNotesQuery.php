<?php

namespace App\Application\Query;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
final readonly class ListNotesQuery
{
    public function __construct(public string $ownerId)
    {
    }
}
