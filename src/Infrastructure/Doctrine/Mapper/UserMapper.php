<?php

namespace App\Infrastructure\Doctrine\Mapper;

use App\Domain\Entity\User;
use App\Domain\Exception\InvalidEmail;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;

final class UserMapper
{
    /**
     * Map domain User to DB row
     *
     * @return array{id: string, email: string, password_hash: string}
     */
    public function toRow(User $user): array
    {
        return [
            'id' => $user->id(),
            'email' => (string)$user->email(), // adapt if email() returns VO or string
            'password_hash' => $user->passwordHash()->toString(),
        ];
    }

    /**
     * Map DB row to domain User
     *
     * @param array{id: string, email: string, password_hash: string} $row
     * @throws InvalidEmail
     */
    public function toDomain(array $row): User
    {
        $email = new Email($row['email']);
        $passwordHash = PasswordHash::fromHash($row['password_hash']);

        return new User($row['id'], $email, $passwordHash);
    }
}
