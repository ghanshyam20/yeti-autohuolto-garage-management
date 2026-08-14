<?php

declare(strict_types=1);

namespace Yeti;

use PDO;

final class Schema
{
    public static function migrate(PDO $pdo): void
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $id = $driver === 'mysql'
            ? 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY'
            : 'INTEGER PRIMARY KEY AUTOINCREMENT';

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS admins (
                id {$id},
                username VARCHAR(150) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at VARCHAR(32) NOT NULL,
                updated_at VARCHAR(32) NOT NULL
            )"
        );

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS bookings (
                id {$id},
                full_name VARCHAR(100) NOT NULL,
                phone_number VARCHAR(25) NOT NULL,
                email VARCHAR(254) NOT NULL,
                vehicle_make VARCHAR(100) NOT NULL,
                vehicle_model VARCHAR(100) NOT NULL,
                registration_number VARCHAR(20) NOT NULL DEFAULT '',
                service_required VARCHAR(50) NOT NULL,
                problem_description TEXT NOT NULL,
                preferred_date VARCHAR(10) NOT NULL,
                preferred_time VARCHAR(30) NOT NULL DEFAULT 'no_preference',
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at VARCHAR(32) NOT NULL,
                updated_at VARCHAR(32) NOT NULL
            )"
        );

        self::createIndex($pdo, 'idx_bookings_status', 'bookings', 'status');
        self::createIndex($pdo, 'idx_bookings_preferred_date', 'bookings', 'preferred_date');
        self::createIndex($pdo, 'idx_bookings_created_at', 'bookings', 'created_at');
    }

    private static function createIndex(PDO $pdo, string $name, string $table, string $column): void
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $pdo->exec("CREATE INDEX IF NOT EXISTS {$name} ON {$table} ({$column})");
            return;
        }

        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.statistics '
            . 'WHERE table_schema = DATABASE() AND table_name = :table AND index_name = :name'
        );
        $statement->execute(['table' => $table, 'name' => $name]);

        if ((int) $statement->fetchColumn() === 0) {
            $pdo->exec("CREATE INDEX {$name} ON {$table} ({$column})");
        }
    }
}
