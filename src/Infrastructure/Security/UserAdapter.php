<?php

namespace App\Infrastructure\Security;

use App\Domain\Entity\User as DomainUser;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserAdapter implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(private DomainUser $user, private array $roles = ['ROLE_USER'])
    {
    }

    public function getUser(): DomainUser
    {
        return $this->user;
    }

    public function getUserIdentifier(): string
    {
        return $this->user->email();
    }

    // For older Symfony versions you can add getUsername() returning the same value
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return $this->user->passwordHash();
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // no-op: domain user stores only the hashed password
    }
}
