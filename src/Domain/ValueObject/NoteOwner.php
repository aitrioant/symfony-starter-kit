<?php

namespace App\Domain\ValueObject;

final readonly class NoteOwner
{
    public function __construct(private string $userId)
    {
        if ($userId === '') {
            throw new \InvalidArgumentException('Owner id cannot be empty.');
        }
    }

    public function __toString(): string
    {
        return $this->userId;
    }

    public function equals(self $other): bool
    {
        return $this->userId === $other->userId;
    }
}
