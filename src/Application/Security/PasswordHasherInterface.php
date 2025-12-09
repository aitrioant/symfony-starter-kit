<?php

namespace App\Application\Security;

interface PasswordHasherInterface
{
    public function hashPasswordFor(string $id, string $email, string $plainPassword): string;
}
