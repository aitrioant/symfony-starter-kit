<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\NoteOwner;
use PHPUnit\Framework\TestCase;

final class NoteOwnerTest extends TestCase
{
    public function testEmptyOwnerThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new NoteOwner('');
    }

    public function testEquals(): void
    {
        $a = new NoteOwner('user-1');
        $b = new NoteOwner('user-1');
        $c = new NoteOwner('user-2');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
        $this->assertSame('user-1', (string)$a);
    }
}
