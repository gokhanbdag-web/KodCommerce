<?php
declare(strict_types=1);

namespace KodCommerce\Support;

use PDO;
use RuntimeException;

final class Installation
{
    private static function configPath(): string
    {
        return dirname(__DIR__, 2) . '/storage/database.php';
    }

    public static function databasePath(): string
    {
        return dirname(__DIR__, 2) . '/storage/kodcommerce.sqlite';
    }

    public static function configured(): bool
    {
        return is_file(self::configPath());
    }

    public static function installed(): bool
    {
        if (!self::configured() && !is_file(self::databasePath())) {
            return false;
        }
        try {
            return (bool) self::connection()->query('SELECT 1 FROM users LIMIT 1')->fetchColumn();
        } catch (\Throwable) {
            return false;
        }
    }

    public static function connection(): PDO
    {
        if (self::configured()) {
            $config = require self::configPath();
            return self::mysqlConnection($config);
        }
        if (is_file(self::databasePath())) {
            return new PDO('sqlite:' . self::databasePath(), null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        throw new RuntimeException('Database is not configured');
    }

    public static function mysqlConnection(array $config): PDO
    {
        if (!extension_loaded('pdo_mysql')) {
            throw new RuntimeException('PDO MySQL extension is required');
        }
        $host = (string) ($config['host'] ?? '');
        $port = (int) ($config['port'] ?? 3306);
        $database = (string) ($config['database'] ?? '');
        $username = (string) ($config['username'] ?? '');
        if ($host === '' || !preg_match('/^[a-zA-Z0-9_.:-]+$/', $host) || $port < 1 || $port > 65535 ||
            !preg_match('/^[a-zA-Z0-9_]+$/', $database) || $username === '') {
            throw new RuntimeException('Invalid database connection settings');
        }
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        return new PDO($dsn, $username, (string) ($config['password'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public static function test(array $config): void
    {
        self::mysqlConnection($config)->query('SELECT 1');
    }

    public static function install(string $email, string $password, array $config = []): void
    {
        if (self::configured() || self::installed() || is_file(self::databasePath())) {
            throw new RuntimeException('Installation already exists; existing data was not changed');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            throw new RuntimeException('Valid email and password of at least 12 characters required');
        }
        $db = self::mysqlConnection($config);
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(190) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(50) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $db->beginTransaction();
        try {
            if ((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() !== 0) {
                throw new RuntimeException('Administrator already exists');
            }
            $stmt = $db->prepare('INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)');
            $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), 'administrator']);
            $db->commit();
        } catch (\Throwable $error) {
            $db->rollBack();
            throw $error;
        }
        $directory = dirname(self::configPath());
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create storage directory');
        }
        $serialized = '<?php return ' . var_export([
            'host' => (string) $config['host'],
            'port' => (int) ($config['port'] ?? 3306),
            'database' => (string) $config['database'],
            'username' => (string) $config['username'],
            'password' => (string) ($config['password'] ?? ''),
        ], true) . ';' . "\n";
        $temp = tempnam($directory, '.database-');
        if ($temp === false) {
            throw new RuntimeException('Cannot create configuration');
        }
        try {
            chmod($temp, 0600);
            if (file_put_contents($temp, $serialized) === false || !rename($temp, self::configPath())) {
                throw new RuntimeException('Cannot save database configuration');
            }
        } finally {
            if (is_file($temp)) {
                unlink($temp);
            }
        }
    }
}
