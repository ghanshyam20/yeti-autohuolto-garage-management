<?php

declare(strict_types=1);

namespace Yeti;

use PDO;

final class AdminRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function findByUsername(string $username): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admins WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);
        $admin = $statement->fetch();

        return $admin === false ? null : $admin;
    }

    public function save(string $username, string $passwordHash): void
    {
        $now = now();
        $existing = $this->findByUsername($username);

        if ($existing !== null) {
            $statement = $this->pdo->prepare(
                'UPDATE admins SET password_hash = :password_hash, updated_at = :updated_at WHERE id = :id'
            );
            $statement->execute([
                'password_hash' => $passwordHash,
                'updated_at' => $now,
                'id' => $existing['id'],
            ]);
            return;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO admins (username, password_hash, created_at, updated_at) '
            . 'VALUES (:username, :password_hash, :created_at, :updated_at)'
        );
        $statement->execute([
            'username' => $username,
            'password_hash' => $passwordHash,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
