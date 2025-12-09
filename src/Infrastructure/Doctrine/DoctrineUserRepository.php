<?php

namespace App\Infrastructure\Doctrine;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Doctrine\Domain\Entity\OrmUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findById(string $id): ?User
    {
        $orm = $this->em->getRepository(OrmUser::class)->find($id);
        return $orm ? $this->toDomain($orm) : null;
    }

    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(OrmUser::class);
    }

    private function toDomain(OrmUser $orm): User
    {
        return new User($orm->getId(), $orm->getEmail(), $orm->getPasswordHash());
    }

    public function findByEmail(string $email): ?User
    {
        $orm = $this->em->getRepository(OrmUser::class)->findOneBy(['email' => $email]);
        return $orm ? $this->toDomain($orm) : null;
    }

    public function save(User $user): void
    {
        $orm = $this->em->getRepository(OrmUser::class)->find($user->id())
            ?? new OrmUser($user->id(), $user->email(), $user->passwordHash());

        $orm->setEmail($user->email());
        $orm->setPasswordHash($user->passwordHash());

        $this->em->persist($orm);
        $this->em->flush();
    }
}
