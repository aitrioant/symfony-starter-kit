<?php

namespace App\Application\Query;

final readonly class ListNotesQuery
{
    public function __construct(public string $ownerId)
    {
    }
}
