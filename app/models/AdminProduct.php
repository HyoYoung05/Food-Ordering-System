<?php
declare(strict_types=1);

final class AdminProduct
{
    public function __construct(private PDO $db) { $this->ensureImageColumn(); }

    public function all(): array
    {
        return array_map([$this,'format'],$this->db->query('SELECT * FROM menu_items ORDER BY id DESC')->fetchAll());
    }

    public function save(array $data, array $actor): array
    {
        $id=(int)($data['id']??0);$name=trim($data['name']??'');$category=trim($data['category']??'');$description=trim($data['description']??'');$price=(float)($data['price']??0);
        if($name===''||$category===''||$description===''||$price<=0)throw new RuntimeException('Name, category, notes, and a valid price are required.');
        $badge=strtoupper(trim($data['badge']??''));$badge=in_array($badge,['NEW','POPULAR','BESTSELLER'],true)?$badge:null;$imagePath=$data['imagePath']??null;
        [$adminId,$staffId]=$this->actorIds($actor);
        if($id){$current=$this->find($id);if(!$current)throw new RuntimeException('Product not found.');$imagePath=$imagePath?:$current['image'];$available=$current['isAvailable']?1:0;$statement=$this->db->prepare('UPDATE menu_items SET name=?,category=?,description=?,price=?,badge=?,is_available=?,image_path=?,updated_by_admin_id=?,updated_by_staff_id=? WHERE id=?');$statement->execute([$name,$category,$description,$price,$badge,$available,$imagePath,$adminId,$staffId,$id]);}
        else{$statement=$this->db->prepare('INSERT INTO menu_items (name,category,description,price,color,badge,is_available,image_path,created_by_admin_id,created_by_staff_id,updated_by_admin_id,updated_by_staff_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');$statement->execute([$name,$category,$description,$price,'#e9d5b5',$badge,1,$imagePath,$adminId,$staffId,$adminId,$staffId]);$id=(int)$this->db->lastInsertId();}
        return $this->find($id);
    }

    public function setAvailability(int $id,bool $available,array $actor): array
    {
        [$adminId,$staffId]=$this->actorIds($actor);$statement=$this->db->prepare('UPDATE menu_items SET is_available=?,updated_by_admin_id=?,updated_by_staff_id=? WHERE id=?');$statement->execute([$available?1:0,$adminId,$staffId,$id]);
        $product=$this->find($id);if(!$product)throw new RuntimeException('Product not found.');return $product;
    }

    private function find(int $id): ?array
    {
        $statement=$this->db->prepare('SELECT * FROM menu_items WHERE id=?');$statement->execute([$id]);$row=$statement->fetch();return $row?$this->format($row):null;
    }

    private function format(array $row): array
    {
        return ['id'=>(int)$row['id'],'name'=>$row['name'],'category'=>$row['category'],'description'=>$row['description'],'price'=>(float)$row['price'],'image'=>$row['image_path']??null,'badge'=>$row['badge'],'isAvailable'=>(bool)$row['is_available']];
    }

    private function ensureImageColumn(): void
    {
        $exists=$this->db->query("SHOW COLUMNS FROM menu_items LIKE 'image_path'")->fetch();
        if(!$exists)$this->db->exec('ALTER TABLE menu_items ADD image_path VARCHAR(255) NULL AFTER color');
    }

    private function actorIds(array $actor): array
    {
        $id=(int)($actor['id']??0);$role=$actor['role']??'';
        if($id<1||!in_array($role,['admin','staff'],true))throw new RuntimeException('Invalid staff attribution.');
        return $role==='admin'?[$id,null]:[null,$id];
    }
}
