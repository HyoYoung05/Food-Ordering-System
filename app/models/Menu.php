<?php
declare(strict_types=1);

final class Menu
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        $items = $this->db->query('SELECT id, name, category, price, color, image_path AS image, description AS `desc`, badge, is_available AS isAvailable FROM menu_items ORDER BY id')->fetchAll();
        return array_map(static function (array $item): array {
            $item['id'] = (int)$item['id'];
            $item['price'] = (float)$item['price'];
            $item['isAvailable'] = (bool)$item['isAvailable'];
            return $item;
        }, $items);
    }

    public function find(int $id): ?array
    {
        $statement=$this->db->prepare('SELECT id,name,category,price,color,image_path AS image,description AS `desc`,badge,is_available AS isAvailable FROM menu_items WHERE id=? LIMIT 1');$statement->execute([$id]);$item=$statement->fetch();
        if(!$item)return null;$item['id']=(int)$item['id'];$item['price']=(float)$item['price'];$item['isAvailable']=(bool)$item['isAvailable'];return $item;
    }
}
