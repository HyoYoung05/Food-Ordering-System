<?php
declare(strict_types=1);

final class Cart
{
    public function __construct(private PDO $db) {}

    public function get(int $customerId): array
    {
        $sql = 'SELECT m.id, m.name, m.category, m.price, m.emoji, m.color, m.description AS `desc`, m.badge, c.quantity AS qty
                FROM cart_items c JOIN menu_items m ON m.id = c.menu_item_id
                WHERE c.customer_id = ? AND m.is_available = 1 ORDER BY c.updated_at DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute([$customerId]);
        return array_map(static function (array $item): array {
            $item['id'] = (int)$item['id']; $item['price'] = (float)$item['price']; $item['qty'] = (int)$item['qty']; return $item;
        }, $statement->fetchAll());
    }

    public function replace(int $customerId, array $items): array
    {
        $this->db->beginTransaction();
        try {
            $delete = $this->db->prepare('DELETE FROM cart_items WHERE customer_id = ?');
            $delete->execute([$customerId]);
            $insert = $this->db->prepare('INSERT INTO cart_items (customer_id, menu_item_id, quantity) VALUES (?, ?, ?)');
            foreach ($items as $item) {
                $quantity = max(1, min(99, (int)($item['qty'] ?? 1)));
                $insert->execute([$customerId, (int)$item['id'], $quantity]);
            }
            $this->db->commit();
        } catch (Throwable $error) {
            $this->db->rollBack(); throw $error;
        }
        return $this->get($customerId);
    }
}
