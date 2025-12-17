<?php

namespace App\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

class Id
{
    private function __construct(
        private string $id
    )
    {
        if (!Uuid::isValid($id)) {
            throw new \InvalidArgumentException(sprintf('Invalid UUID: "%s".', $id));
        }
    }

    public static function new(): self
    {
        return new self(Uuid::v4()->toRfc4122());
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
