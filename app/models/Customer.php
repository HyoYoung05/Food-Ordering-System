<?php
declare(strict_types=1);

final class Customer
{
    public function __construct(private PDO $db) {}

    public function authenticate(string $identifier, string $password): array
    {
        $identifier = strtolower(trim($identifier));
        $statement = $this->db->prepare('SELECT * FROM customers WHERE LOWER(email) = ? OR LOWER(username) = ? LIMIT 1');
        $statement->execute([$identifier, $identifier]);
        $customer = $statement->fetch();
        if (!$customer || !password_verify($password, $customer['password_hash'])) throw new RuntimeException('Invalid username/email or password.');
        return $this->publicData($customer);
    }

    public function register(string $firstName, string $surname, string $username, string $email, string $password, string $phone, string $phoneCountry, string $address, string $country, string $zipCode): array
    {
        $firstName=trim($firstName);$surname=trim($surname);$name=trim($firstName.' '.$surname);$username=strtolower(trim($username));$email=strtolower(trim($email));
        if(!preg_match('/^[a-z0-9_]{3,30}$/',$username)) throw new RuntimeException('Username must be 3–30 characters using letters, numbers, or underscores.');
        $check=$this->db->prepare('SELECT id FROM customers WHERE LOWER(email)=? OR LOWER(username)=? LIMIT 1');$check->execute([$email,$username]);
        if($check->fetch()) throw new RuntimeException('That username or email is already registered.');
        $statement=$this->db->prepare('INSERT INTO customers (full_name,first_name,surname,username,email,password_hash,phone,phone_country,delivery_address,country,zip_code) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $statement->execute([$name,$firstName,$surname,$username,$email,password_hash($password,PASSWORD_DEFAULT),trim($phone),trim($phoneCountry),trim($address),trim($country),trim($zipCode)]);
        return ['id'=>(int)$this->db->lastInsertId(),'name'=>$name,'firstName'=>$firstName,'surname'=>$surname,'username'=>$username,'email'=>$email,'phone'=>trim($phone),'phoneCountry'=>trim($phoneCountry),'address'=>trim($address),'country'=>trim($country),'zipCode'=>trim($zipCode)];
    }

    public function updateProfile(int $id, array $data): array
    {
        $firstName=trim($data['firstName']);$surname=trim($data['surname']);$username=strtolower(trim($data['username']));$email=strtolower(trim($data['email']));
        if(!preg_match('/^[a-z0-9_]{3,30}$/',$username)) throw new RuntimeException('Username must be 3–30 characters using letters, numbers, or underscores.');
        $check=$this->db->prepare('SELECT id FROM customers WHERE (LOWER(email)=? OR LOWER(username)=?) AND id<>? LIMIT 1');$check->execute([$email,$username,$id]);
        if($check->fetch()) throw new RuntimeException('That username or email is already used by another customer.');
        $statement=$this->db->prepare('UPDATE customers SET full_name=?,first_name=?,surname=?,username=?,email=?,phone=?,phone_country=?,delivery_address=?,country=?,zip_code=? WHERE id=?');
        $statement->execute([trim($firstName.' '.$surname),$firstName,$surname,$username,$email,trim($data['phone']),trim($data['phoneCountry']),trim($data['address']),trim($data['country']),trim($data['zipCode']),$id]);
        $query=$this->db->prepare('SELECT * FROM customers WHERE id=?');$query->execute([$id]);
        return $this->publicData($query->fetch());
    }

    private function publicData(array $customer): array
    {
        return ['id'=>(int)$customer['id'],'name'=>$customer['full_name'],'firstName'=>$customer['first_name']??'','surname'=>$customer['surname']??'','username'=>$customer['username'],'email'=>$customer['email'],'phone'=>$customer['phone']??'','phoneCountry'=>$customer['phone_country']??'','address'=>$customer['delivery_address']??'','country'=>$customer['country']??'','zipCode'=>$customer['zip_code']??''];
    }
}
