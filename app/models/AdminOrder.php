<?php
declare(strict_types=1);

final class AdminOrder
{
    private const STATUSES = ['Order placed','Preparing','Out for delivery','Delivered','Cancelled'];

    public function __construct(private PDO $db) {}

    public function dashboard(): array
    {
        $orders = $this->db->query("SELECT o.id,o.order_number,o.status,o.total,o.payment_method,o.delivery_address,o.phone,o.created_at,c.full_name AS customer,c.email,
            GROUP_CONCAT(CONCAT(oi.quantity,'x ',oi.item_name) ORDER BY oi.id SEPARATOR ' · ') AS items
            FROM orders o JOIN customers c ON c.id=o.customer_id JOIN order_items oi ON oi.order_id=o.id
            GROUP BY o.id ORDER BY o.created_at DESC LIMIT 100")->fetchAll();
        $stats = $this->db->query("SELECT COUNT(*) total_orders,
            COALESCE(SUM(CASE WHEN DATE(created_at)=CURDATE() THEN total ELSE 0 END),0) today_revenue,
            SUM(status IN ('Order placed','Preparing')) active_orders,
            COUNT(DISTINCT customer_id) customers FROM orders")->fetch();
        return ['orders'=>$orders,'stats'=>[
            'totalOrders'=>(int)$stats['total_orders'], 'todayRevenue'=>(float)$stats['today_revenue'],
            'activeOrders'=>(int)$stats['active_orders'], 'customers'=>(int)$stats['customers']
        ]];
    }

    public function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, self::STATUSES, true)) throw new RuntimeException('Invalid order status.');
        $this->db->beginTransaction();
        try {
            $statement=$this->db->prepare('UPDATE orders SET status=? WHERE id=?'); $statement->execute([$status,$id]);
            if ($statement->rowCount()===0) throw new RuntimeException('Order not found or already updated.');
            $this->db->prepare('INSERT INTO order_status_history (order_id,status) VALUES (?,?)')->execute([$id,$status]);
            $this->db->commit();
        } catch (Throwable $error) { $this->db->rollBack(); throw $error; }
    }
}
