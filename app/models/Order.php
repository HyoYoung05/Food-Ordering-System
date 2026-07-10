<?php
declare(strict_types=1);

final class Order
{
    public function __construct(private PDO $db) {}

    public function allForCustomer(int $customerId): array
    {
        $statement = $this->db->prepare('SELECT o.*, c.full_name AS customer, c.email FROM orders o JOIN customers c ON c.id=o.customer_id WHERE o.customer_id=? ORDER BY o.created_at DESC');
        $statement->execute([$customerId]);
        $orders = $statement->fetchAll();
        $itemQuery = $this->db->prepare('SELECT menu_item_id AS id, item_name AS name, unit_price AS price, quantity AS qty FROM order_items WHERE order_id=?');
        foreach ($orders as &$order) {
            $itemQuery->execute([$order['id']]);
            $order['items'] = array_map(static function (array $item): array {
                $item['id']=(int)$item['id']; $item['price']=(float)$item['price']; $item['qty']=(int)$item['qty']; return $item;
            }, $itemQuery->fetchAll());
            $order = $this->format($order);
        }
        return $orders;
    }

    public function create(int $customerId, array $payload): array
    {
        $this->db->beginTransaction();
        try {
            $cart = (new Cart($this->db))->get($customerId);
            if (!$cart) throw new RuntimeException('Your cart is empty.');
            $subtotal = array_reduce($cart, fn(float $sum, array $item): float => $sum + $item['price'] * $item['qty'], 0.0);
            $delivery = 49.0; $number = date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $insert = $this->db->prepare('INSERT INTO orders (order_number,customer_id,delivery_address,phone,payment_method,notes,subtotal,delivery_fee,total) VALUES (?,?,?,?,?,?,?,?,?)');
            $insert->execute([$number,$customerId,trim($payload['address']),trim($payload['phone']),$payload['payment'],$payload['notes'] ?? null,$subtotal,$delivery,$subtotal+$delivery]);
            $orderId = (int)$this->db->lastInsertId();
            $itemInsert = $this->db->prepare('INSERT INTO order_items (order_id,menu_item_id,item_name,unit_price,quantity) VALUES (?,?,?,?,?)');
            foreach ($cart as $item) $itemInsert->execute([$orderId,$item['id'],$item['name'],$item['price'],$item['qty']]);
            $this->db->prepare('INSERT INTO order_status_history (order_id,status) VALUES (?,?)')->execute([$orderId,'Order placed']);
            $this->db->prepare('DELETE FROM cart_items WHERE customer_id=?')->execute([$customerId]);
            $this->db->commit();
            return $this->allForCustomer($customerId)[0];
        } catch (Throwable $error) {
            $this->db->rollBack(); throw $error;
        }
    }

    public function cancel(int $customerId, string $orderNumber): array
    {
        $this->db->beginTransaction();
        try {
            $statement=$this->db->prepare("UPDATE orders SET status='Cancelled' WHERE customer_id=? AND order_number=? AND status='Order placed'");
            $statement->execute([$customerId,$orderNumber]);
            if($statement->rowCount()!==1)throw new RuntimeException('This order can no longer be cancelled because preparation has started or it was already cancelled.');
            $idQuery=$this->db->prepare('SELECT id FROM orders WHERE customer_id=? AND order_number=?');$idQuery->execute([$customerId,$orderNumber]);
            $orderId=(int)$idQuery->fetchColumn();
            $this->db->prepare("INSERT INTO order_status_history (order_id,status) VALUES (?,'Cancelled')")->execute([$orderId]);
            $this->db->commit();
            return $this->allForCustomer($customerId);
        } catch(Throwable $error){$this->db->rollBack();throw $error;}
    }

    private function format(array $order): array
    {
        return ['id'=>$order['order_number'],'databaseId'=>(int)$order['id'],'createdAt'=>strtotime($order['created_at'])*1000,
            'email'=>$order['email'],'customer'=>$order['customer'],'items'=>$order['items'],'subtotal'=>(float)$order['subtotal'],
            'delivery'=>(float)$order['delivery_fee'],'total'=>(float)$order['total'],'address'=>$order['delivery_address'],
            'phone'=>$order['phone'],'payment'=>$order['payment_method'],'notes'=>$order['notes'],'status'=>$order['status']];
    }
}
