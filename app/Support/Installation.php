<?php
declare(strict_types=1);

namespace KodCommerce\Support;

use PDO;
use RuntimeException;

final class Installation
{
    public static function databasePath(): string
    {
        return dirname(__DIR__, 2) . '/storage/kodcommerce.sqlite';
    }

    public static function installed(): bool
    {
        if (!is_file(self::databasePath())) {
            return false;
        }
        try {
            $db = self::connection();
            return (bool) $db->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='users'")->fetchColumn()
                && (bool) $db->query('SELECT 1 FROM users LIMIT 1')->fetchColumn();
        } catch (\Throwable) {
            return false;
        }
    }

    public static function connection(): PDO
    {
        $db = new PDO('sqlite:' . self::databasePath(), null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $db->exec('PRAGMA busy_timeout = 5000');
        return $db;
    }

    public static function install(string $email, string $password): void
    {
        if (self::installed()) {
            throw new RuntimeException('Already installed');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            throw new RuntimeException('Valid email and password of at least 12 characters required');
        }
        if (!extension_loaded('pdo_sqlite')) {
            throw new RuntimeException('PDO SQLite extension is required');
        }
        $directory = dirname(self::databasePath());
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create storage directory');
        }
        $db = self::connection();
        $db->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL, role TEXT NOT NULL)');
        $db->beginTransaction();
        try {
            if ($db->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
                throw new RuntimeException('Already installed');
            }
            $stmt = $db->prepare('INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)');
            $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), 'administrator']);
            $db->commit();
        } catch (\Throwable $error) {
            $db->rollBack();
            throw $error;
        }
        @chmod(self::databasePath(), 0600);
    }
}
