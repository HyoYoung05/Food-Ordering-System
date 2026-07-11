<?php
declare(strict_types=1);

final class AdminProduct
{
    public function __construct(private PDO $db) { $this->ensureImageColumn(); }

    public function all(): array
    {
        return array_map([$this,'format'],$this->db->query('SELECT * FROM menu_items ORDER BY id DESC')->fetchAll());
    }

    public function save(array $data): array
    {
        $id=(int)($data['id']??0);$name=trim($data['name']??'');$category=trim($data['category']??'');$description=trim($data['description']??'');$price=(float)($data['price']??0);
        if($name===''||$category===''||$description===''||$price<=0)throw new RuntimeException('Name, category, notes, and a valid price are required.');
        $badge=strtoupper(trim($data['badge']??''));$badge=in_array($badge,['NEW','POPULAR','BESTSELLER'],true)?$badge:null;$imagePath=$data['imagePath']??null;
        if($id){$current=$this->find($id);if(!$current)throw new RuntimeException('Product not found.');$imagePath=$imagePath?:$current['image'];$available=$current['isAvailable']?1:0;$statement=$this->db->prepare('UPDATE menu_items SET name=?,category=?,description=?,price=?,badge=?,is_available=?,image_path=? WHERE id=?');$statement->execute([$name,$category,$description,$price,$badge,$available,$imagePath,$id]);}
        else{$statement=$this->db->prepare('INSERT INTO menu_items (name,category,description,price,color,badge,is_available,image_path) VALUES (?,?,?,?,?,?,?,?)');$statement->execute([$name,$category,$description,$price,'#e9d5b5',$badge,1,$imagePath]);$id=(int)$this->db->lastInsertId();}
        return $this->find($id);
    }

    public function setAvailability(int $id,bool $available): array
    {
        $statement=$this->db->prepare('UPDATE menu_items SET is_available=? WHERE id=?');$statement->execute([$available?1:0,$id]);
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
}
