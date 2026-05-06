<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'mysql';
            $db   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'facturo';
            $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'facturo';
            $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: 'facturo';

            self::$instance = new PDO(
                "mysql:host={$host};dbname={$db};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }

        return self::$instance;
    }
}
