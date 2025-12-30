<?php

namespace App\Infrastructure\Doctrine;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\PasswordHash;
use App\Infrastructure\Doctrine\Domain\Entity\OrmUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(readonly EntityManagerInterface $em)
    {
    }

    public function findById(string $id): ?User
    {
        /** @var OrmUser|null $orm */
        $orm = $this->getRepository()->find($id);
        return $orm ? $this->toDomain($orm) : null;
    }

    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(OrmUser::class);
    }

    private function toDomain(OrmUser $orm): User
    {
        return new User(Id::fromString($orm->getId()), new Email($orm->getEmail()), PasswordHash::fromHash($orm->getPasswordHash()));
    }

    public function findByEmail(string $email): ?User
    {
        $orm = $this->em->getRepository(OrmUser::class)->findOneBy(['email' => $email]);
        return $orm ? $this->toDomain($orm) : null;
    }

    public function save(User $user): void
    {
        $orm = $this->em->getRepository(OrmUser::class)->find($user->id())
            ?? new OrmUser($user->id(), $user->email(), $user->passwordHash()->toString());

        $orm->setEmail($user->email());
        $orm->setPasswordHash($user->passwordHash()->toString());

        $this->em->persist($orm);
        $this->em->flush();
    }
}
