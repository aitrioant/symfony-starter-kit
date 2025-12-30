<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\InvalidNoteContent;
use App\Domain\ValueObject\NoteContent;
use PHPUnit\Framework\TestCase;

final class NoteContentTest extends TestCase
{
    public function testConstructEmptyThrows(): void
    {
        $this->expectException(InvalidNoteContent::class);
        new NoteContent('   ');
    }

    public function testConstructTooLongThrows(): void
    {
        $this->expectException(InvalidNoteContent::class);
        new NoteContent(str_repeat('a', NoteContent::MAX_LENGTH + 1));
    }

    public function testToStringAndEquals(): void
    {
        $c1 = new NoteContent('hello');
        $c2 = new NoteContent('hello');
        $c3 = new NoteContent('other');

        $this->assertSame('hello', (string)$c1);
        $this->assertTrue($c1->equals($c2));
        $this->assertFalse($c1->equals($c3));
    }
}
