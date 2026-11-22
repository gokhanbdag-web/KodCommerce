<?php
declare(strict_types=1);

namespace KodCommerce\Support;

final class I18n
{
    public static function boot(string $locale): void
    {
        $locale = preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale) ? $locale : 'en_US';
        $path = dirname(__DIR__, 2) . '/locales';
        if (!function_exists('bindtextdomain')) {
            return;
        }

        setlocale(LC_MESSAGES, $locale . '.UTF-8', $locale, 'en_US.UTF-8');
        bindtextdomain('kodcommerce', $path);
        bind_textdomain_codeset('kodcommerce', 'UTF-8');
        textdomain('kodcommerce');
    }

    public static function translate(string $message): string
    {
        return function_exists('gettext') ? gettext($message) : $message;
    }
}
