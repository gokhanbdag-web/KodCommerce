<?php
declare(strict_types=1);

namespace KodCommerce\Support;

final class I18n
{
    public const SUPPORTED = ['en_US', 'tr_TR'];

    public static function boot(string $locale): void
    {
        $locale = in_array($locale, self::SUPPORTED, true) ? $locale : 'en_US';
        if (!function_exists('bindtextdomain')) {
            return;
        }
        $path = dirname(__DIR__, 2) . '/locales';
        setlocale(LC_MESSAGES, $locale . '.UTF-8', $locale . '.utf8', $locale, 'en_US.UTF-8');
        bindtextdomain('kodcommerce', $path);
        bind_textdomain_codeset('kodcommerce', 'UTF-8');
        textdomain('kodcommerce');
    }

    public static function translate(string $message): string
    {
        return function_exists('gettext') ? gettext($message) : $message;
    }

    public static function locale(): string
    {
        return $_SESSION['locale'] ?? 'en_US';
    }

    /** Javascript messages use the same gettext domain as PHP. */
    public static function clientMessages(): array
    {
        $messages = ['Unable to sign in', 'Navigation failed', 'Network error', 'Language', 'English', 'Turkish'];
        return array_combine($messages, array_map(self::translate(...), $messages));
    }
}
