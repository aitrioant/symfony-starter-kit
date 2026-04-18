<?php

namespace App\Application\Service;

interface UserExistsChecker
{
    public function exists(string $userId): bool;
}