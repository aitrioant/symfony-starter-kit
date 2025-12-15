<?php

namespace App\Application\Security;

interface PasswordHasherInterface
{
    public function hashPassword(string $plainPassword): string;
}
