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

    public function revenue(string $period): array
    {
        $settings=[
            'daily'=>['days'=>14,'group'=>"DATE(created_at)",'label'=>"DATE_FORMAT(created_at,'%Y-%m-%d')"],
            'weekly'=>['days'=>84,'group'=>"YEARWEEK(created_at,1)",'label'=>"DATE_FORMAT(DATE_SUB(DATE(created_at), INTERVAL WEEKDAY(created_at) DAY),'%Y-%m-%d')"],
            'monthly'=>['days'=>365,'group'=>"DATE_FORMAT(created_at,'%Y-%m')",'label'=>"DATE_FORMAT(created_at,'%Y-%m-01')"],
        ];
        if(!isset($settings[$period]))throw new RuntimeException('Invalid revenue period.');
        $setting=$settings[$period];
        $sql="SELECT {$setting['label']} period_label,COUNT(*) order_count,SUM(total) amount FROM orders WHERE status<>'Cancelled' AND created_at>=DATE_SUB(NOW(),INTERVAL {$setting['days']} DAY) GROUP BY {$setting['group']} ORDER BY MIN(created_at)";
        $rows=$this->db->query($sql)->fetchAll();
        $points=array_map(function(array $row)use($period):array{
            $date=new DateTimeImmutable($row['period_label']);
            $label=match($period){'daily'=>$date->format('M j'),'weekly'=>'Week '.$date->format('M j'),'monthly'=>$date->format('M Y')};
            return ['label'=>$label,'date'=>$row['period_label'],'amount'=>(float)$row['amount'],'orders'=>(int)$row['order_count']];
        },$rows);
        return ['period'=>$period,'points'=>$points,'total'=>array_sum(array_column($points,'amount')),'orders'=>array_sum(array_column($points,'orders')),'generatedAt'=>(new DateTimeImmutable('now',new DateTimeZone('Asia/Manila')))->format(DateTimeInterface::ATOM)];
    }

    public function details(int $orderId): array
    {
        $statement=$this->db->prepare('SELECT o.*,c.full_name,c.first_name,c.surname,c.username,c.email,c.phone AS customer_phone,c.phone_country,c.delivery_address AS saved_address,c.country,c.zip_code FROM orders o JOIN customers c ON c.id=o.customer_id WHERE o.id=?');
        $statement->execute([$orderId]);$order=$statement->fetch();
        if(!$order)throw new RuntimeException('Order not found.');
        $items=$this->db->prepare('SELECT item_name AS name,unit_price AS price,quantity AS qty FROM order_items WHERE order_id=? ORDER BY id');$items->execute([$orderId]);
        $history=$this->db->prepare('SELECT status,created_at FROM order_status_history WHERE order_id=? ORDER BY created_at,id');$history->execute([$orderId]);
        return [
            'customer'=>['id'=>(int)$order['customer_id'],'name'=>$order['full_name'],'firstName'=>$order['first_name'],'surname'=>$order['surname'],'username'=>$order['username'],'email'=>$order['email'],'phone'=>trim(($order['phone_country']??'').' '.($order['customer_phone']??'')),'address'=>trim(implode(', ',array_filter([$order['saved_address'],$order['zip_code'],$order['country']])))],
            'order'=>['id'=>(int)$order['id'],'number'=>$order['order_number'],'status'=>$order['status'],'createdAt'=>$order['created_at'],'deliveryAddress'=>$order['delivery_address'],'phone'=>$order['phone'],'payment'=>$order['payment_method'],'notes'=>$order['notes'],'subtotal'=>(float)$order['subtotal'],'delivery'=>(float)$order['delivery_fee'],'total'=>(float)$order['total'],'items'=>array_map(fn($item)=>['name'=>$item['name'],'price'=>(float)$item['price'],'qty'=>(int)$item['qty']],$items->fetchAll()),'history'=>$history->fetchAll()]
        ];
    }
}
