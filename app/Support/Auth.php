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
        $expectedEmail = getenv('KODCOMMERCE_ADMIN_EMAIL') ?: '';
        $hash = getenv('KODCOMMERCE_ADMIN_PASSWORD_HASH') ?: '';
        if ($expectedEmail === '' || $hash === '' || !hash_equals($expectedEmail, $email) || !password_verify($password, $hash)) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['user'] = ['email' => $email, 'role' => 'administrator'];
        return true;
    }

    public static function allowed(string $path): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }
        // Stage 1 supports only the bootstrap administrator; no fake staff/customer accounts.
        return $user['role'] === 'administrator' && isset(Navigation::pages()[$path]);
    }
}
