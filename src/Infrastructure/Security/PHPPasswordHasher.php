<?php

namespace App\Infrastructure\Security;

use App\Application\Security\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;

class PHPPasswordHasher implements PasswordHasherInterface
{
    public function __construct(private NativePasswordHasher $nativePasswordHasher)
    {
    }

    public function hashPassword(string $plainPassword): string
    {
        return $this->nativePasswordHasher->hash($plainPassword);
    }

}
