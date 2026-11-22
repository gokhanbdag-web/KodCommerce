<?php
declare(strict_types=1);

namespace KodCommerce\Support;

use PDO;
use RuntimeException;

final class Pages
{
    public static function ensureSchema(): void
    {
        $db = Installation::connection();
        $db->exec('CREATE TABLE IF NOT EXISTS cms_pages (id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT NOT NULL UNIQUE, title TEXT NOT NULL, status TEXT NOT NULL DEFAULT \'draft\', layout_json TEXT NOT NULL, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
    }

    public static function home(): ?array
    {
        self::ensureSchema();
        $statement = Installation::connection()->prepare('SELECT * FROM cms_pages WHERE slug = ? AND status = ? LIMIT 1');
        $statement->execute(['home', 'published']);
        return $statement->fetch() ?: null;
    }

    public static function saveHome(string $title, array $layout): void
    {
        if (trim($title) === '' || strlen($title) > 160) {
            throw new RuntimeException('Page title is required (max 160 characters)');
        }
        if (count($layout) > 40) {
            throw new RuntimeException('Too many rows');
        }
        foreach ($layout as $row) {
            if (!is_array($row) || !in_array($row['width'] ?? '', ['contained', 'fluid', 'full'], true) || !isset($row['columns']) || !is_array($row['columns']) || count($row['columns']) > 12) {
                throw new RuntimeException('Invalid row layout');
            }
            foreach ($row['columns'] as $column) {
                if (!is_array($column) || !isset($column['span'], $column['widgets']) || !is_int($column['span']) || $column['span'] < 1 || $column['span'] > 12 || !is_array($column['widgets']) || count($column['widgets']) > 30) {
                    throw new RuntimeException('Invalid column layout');
                }
                foreach ($column['widgets'] as $widget) {
                    if (!is_array($widget) || !in_array($widget['type'] ?? '', ['text', 'heading', 'button'], true) || !is_string($widget['text'] ?? null) || strlen($widget['text']) > 5000) {
                        throw new RuntimeException('Invalid widget');
                    }
                }
            }
        }
        self::ensureSchema();
        $json = json_encode($layout, JSON_THROW_ON_ERROR);
        $statement = Installation::connection()->prepare("INSERT INTO cms_pages (slug,title,status,layout_json) VALUES ('home',?,'published',?) ON CONFLICT(slug) DO UPDATE SET title=excluded.title, status=excluded.status, layout_json=excluded.layout_json, updated_at=CURRENT_TIMESTAMP");
        $statement->execute([$title, $json]);
    }

    public static function render(?array $page): string
    {
        if (!$page) {
            return '';
        }
        $rows = json_decode($page['layout_json'], true, 512, JSON_THROW_ON_ERROR);
        $html = '';
        foreach ($rows as $row) {
            $container = match ($row['width']) {
                'full' => 'container-fluid px-0',
                'fluid' => 'container-fluid',
                default => 'container',
            };
            $html .= '<section class="py-4"><div class="' . $container . '"><div class="row g-3">';
            foreach ($row['columns'] as $column) {
                $span = (int) $column['span'];
                $html .= '<div class="col-12 col-lg-' . $span . '">';
                foreach ($column['widgets'] as $widget) {
                    $value = htmlspecialchars($widget['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $html .= match ($widget['type']) {
                        'heading' => '<h2>' . $value . '</h2>',
                        'button' => '<span class="btn btn-primary">' . $value . '</span>',
                        default => '<p>' . nl2br($value) . '</p>',
                    };
                }
                $html .= '</div>';
            }
            $html .= '</div></div></section>';
        }
        return $html;
    }
}
