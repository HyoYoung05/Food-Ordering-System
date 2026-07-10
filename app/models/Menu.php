<?php
declare(strict_types=1);

final class Menu
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        $items = $this->db->query('SELECT id, name, category, price, emoji, color, description AS `desc`, badge FROM menu_items WHERE is_available = 1 ORDER BY id')->fetchAll();
        return array_map(static function (array $item): array {
            $item['id'] = (int)$item['id'];
            $item['price'] = (float)$item['price'];
            return $item;
        }, $items);
    }
}
