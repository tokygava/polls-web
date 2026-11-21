<?php

declare(strict_types=1);

final class User
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function create(string $name, string $email, string $passwordHash): ?array
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)
             ON CONFLICT (lower(email)) DO NOTHING
             RETURNING id, name'
        );
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);

        $user = $statement->fetch();
        return $user === false ? null : $user;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, password_hash FROM users WHERE lower(email) = lower(:email)'
        );
        $statement->execute(['email' => $email]);

        $user = $statement->fetch();
        return $user === false ? null : $user;
    }
}
