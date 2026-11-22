<?php
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'KodCommerce\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

use KodCommerce\Support\Auth;
use KodCommerce\Support\I18n;
use KodCommerce\Support\Navigation;

session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax']);
session_start();
I18n::boot(getenv('KODCOMMERCE_LOCALE') ?: 'en_US');

function t(string $message): string { return I18n::translate($message); }
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function jsonResponse(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_THROW_ON_ERROR);
    exit;
}

$path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!\KodCommerce\Support\Installation::installed()) {
    if ($path === 'install' && $method === 'POST') {
        if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
            jsonResponse(['error' => 'Invalid security token'], 419);
        }
        try {
            \KodCommerce\Support\Installation::install(trim((string) ($_POST['email'] ?? '')), (string) ($_POST['password'] ?? ''));
            jsonResponse(['ok' => true]);
        } catch (\Throwable $error) {
            jsonResponse(['error' => $error->getMessage()], 422);
        }
    }
    if ($path === 'install' && $method === 'GET') {
        $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
        require dirname(__DIR__) . '/views/install.php';
        exit;
    }
    if (str_starts_with($path, 'api/')) {
        jsonResponse(['error' => 'Installation required'], 503);
    }
    header('Location: /install', true, 302);
    exit;
}
if ($path === 'install') {
    header('Location: /', true, 302);
    exit;
}
if ($path === 'api/login' && $method === 'POST') {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        jsonResponse(['error' => t('Invalid security token')], 419);
    }
    if (!Auth::attempt((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
        jsonResponse(['error' => t('Invalid credentials')], 401);
    }
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    jsonResponse(['ok' => true, 'csrf' => $_SESSION['csrf'], 'role' => Auth::user()['role']]);
}
if ($path === 'api/logout' && $method === 'POST') {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        jsonResponse(['error' => t('Invalid security token')], 419);
    }
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    jsonResponse(['ok' => true, 'csrf' => $_SESSION['csrf']]);
}

$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
if ($path === 'api/pages/home' && $method === 'POST') {
    if (!Auth::user() || Auth::user()['role'] !== 'administrator') {
        jsonResponse(['error' => 'Forbidden'], 403);
    }
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        jsonResponse(['error' => 'Invalid security token'], 419);
    }
    try {
        $layout = json_decode((string) ($_POST['layout'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($layout) || !array_is_list($layout)) {
            throw new RuntimeException('Invalid layout');
        }
        \\KodCommerce\\Support\\Pages::saveHome((string) ($_POST['title'] ?? ''), $layout);
        jsonResponse(['ok' => true]);
    } catch (\\Throwable $error) {
        jsonResponse(['error' => $error->getMessage()], 422);
    }
}
if ($path === '' && $method === 'GET') {
    require dirname(__DIR__) . '/views/storefront.php';
    exit;
}
$selected = str_starts_with($path, 'admin/') ? substr($path, 6) : 'dashboard/overview';
$pages = Navigation::pages();
if (!isset($pages[$selected])) {
    http_response_code(404);
    $selected = 'dashboard/overview';
}
if ($path !== '' && str_starts_with($path, 'admin/') && !Auth::allowed($selected)) {
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        jsonResponse(['error' => t('Authentication required')], 401);
    }
    http_response_code(403);
}
[$group, $label] = $pages[$selected];
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
    if (!Auth::allowed($selected)) {
        jsonResponse(['error' => t('Authentication required')], 401);
    }
    ob_start();
    require dirname(__DIR__) . '/views/placeholder.php';
    jsonResponse(['html' => ob_get_clean(), 'title' => t($label)]);
}
require dirname(__DIR__) . '/views/layout.php';
