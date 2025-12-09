<?php

namespace App\Infrastructure\Security;

use App\Application\Security\PasswordHasherInterface;
use App\Domain\Entity\User as DomainUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(private UserPasswordHasherInterface $symfonyHasher)
    {
    }

    public function hashPasswordFor(string $id, string $email, string $plainPassword): string
    {
        $transient = new DomainUser($id, $email, '');
        $adapter = new UserAdapter($transient);

        return $this->symfonyHasher->hashPassword($adapter, $plainPassword);
    }

}
