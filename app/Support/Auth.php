<?php
declare(strict_types=1);

namespace KodCommerce\Support;

final class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function attempt(string $email, string $password): bool
    {
        if (!Installation::installed()) {
            return false;
        }
        $stmt = Installation::connection()->prepare('SELECT email, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['user'] = ['email' => $user['email'], 'role' => $user['role']];
        return true;
    }

    public static function allowed(string $path): bool
    {
        $user = self::user();
        return $user !== null && $user['role'] === 'administrator' && isset(Navigation::pages()[$path]);
    }
}
