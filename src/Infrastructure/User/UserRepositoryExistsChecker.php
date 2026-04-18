<?php

namespace App\Infrastructure\User;

use App\Application\Service\UserExistsChecker;
use App\Domain\Repository\UserRepositoryInterface;

final readonly class UserRepositoryExistsChecker implements UserExistsChecker
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function exists(string $userId): bool
    {
        return $this->users->findById($userId) !== null;
    }
}