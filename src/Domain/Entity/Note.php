<?php

namespace App\Domain\Entity;

use App\Domain\Exception\NotOwnerException;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\NoteContent;
use App\Domain\ValueObject\NoteOwner;

final class Note
{
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly Id        $id,
        private NoteContent        $content,
        private readonly NoteOwner $owner,
        private bool               $archived = false,
        ?\DateTimeImmutable        $createdAt = null,
        ?\DateTimeImmutable        $updatedAt = null)
    {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? $this->createdAt;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function content(): NoteContent
    {
        return $this->content;
    }

    public function owner(): NoteOwner
    {
        return $this->owner;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function updateContent(NoteContent $content, NoteOwner $actor): void
    {
        if (!$this->owner->equals($actor)) {
            throw new NotOwnerException('Only the owner can update the note.');
        }
        $this->content = $content;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function archive(NoteOwner $actor): void
    {
        if (!$this->owner->equals($actor)) {
            throw new NotOwnerException('Only the owner can archive the note.');
        }
        $this->archived = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => (string)$this->id,
            'content' => (string)$this->content,
            'owner' => (string)$this->owner,
            'archived' => $this->archived,
            'createdAt' => $this->createdAt->format(DATE_ATOM),
            'updatedAt' => $this->updatedAt->format(DATE_ATOM),
        ];
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
