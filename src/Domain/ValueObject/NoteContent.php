<?php

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidNoteContent;

final readonly class NoteContent
{
    public const int MAX_LENGTH = 1000;
    private string $content;
    
    public function __construct(string $content)
    {
        $this->content = trim($content);
        if ($this->content === '') {
            throw new InvalidNoteContent('Content cannot be empty.');
        }
        if (\mb_strlen($this->content) > self::MAX_LENGTH) {
            throw new InvalidNoteContent(sprintf('Content cannot exceed %d characters.', self::MAX_LENGTH));
        }
    }

    public function __toString(): string
    {
        return $this->content;
    }

    public function equals(self $other): bool
    {
        return $this->content === $other->content;
    }
}
