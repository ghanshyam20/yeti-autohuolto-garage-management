<?php

declare(strict_types=1);

namespace Yeti;

use PDO;

final class Database
{
    private PDO $pdo;

    /** @param array<string, mixed> $config */
    public function __construct(array $config)
    {
        $driver = (string) ($config['driver'] ?? 'mysql');

        if ($driver === 'sqlite') {
            $database = (string) ($config['database'] ?? ':memory:');
            $dsn = 'sqlite:' . $database;
            $username = null;
            $password = null;
        } elseif ($driver === 'mysql') {
            $host = (string) ($config['host'] ?? 'localhost');
            $port = (int) ($config['port'] ?? 3306);
            $database = (string) ($config['database'] ?? '');
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $username = (string) ($config['username'] ?? '');
            $password = (string) ($config['password'] ?? '');
        } else {
            throw new \InvalidArgumentException('Unsupported database driver.');
        }

        $this->pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function connection(): PDO
    {
        return $this->pdo;
    }
}
