<?php
declare(strict_types=1);

final class Menu
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        $items = $this->db->query('SELECT id, name, category, price, emoji, color, image_path AS image, description AS `desc`, badge, is_available AS isAvailable FROM menu_items ORDER BY id')->fetchAll();
        return array_map(static function (array $item): array {
            $item['id'] = (int)$item['id'];
            $item['price'] = (float)$item['price'];
            $item['isAvailable'] = (bool)$item['isAvailable'];
            return $item;
        }, $items);
    }
}
