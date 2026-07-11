<?php
declare(strict_types=1);

final class Customer
{
    public function __construct(private PDO $db) { $this->ensureVerificationColumns(); }

    public function authenticate(string $identifier, string $password): array
    {
        $identifier = strtolower(trim($identifier));
        $statement = $this->db->prepare('SELECT * FROM customers WHERE LOWER(email) = ? OR LOWER(username) = ? LIMIT 1');
        $statement->execute([$identifier, $identifier]);
        $customer = $statement->fetch();
        if (!$customer || !password_verify($password, $customer['password_hash'])) throw new RuntimeException('Invalid username/email or password.');
        if(empty($customer['email_verified_at']))throw new RuntimeException('Verify your email address before logging in. Check your inbox for the Savorly verification message.');
        return $this->publicData($customer);
    }

    public function register(string $firstName, string $surname, string $username, string $email, string $password, string $phone, string $phoneCountry, string $address, string $country, string $zipCode): array
    {
        $firstName=trim($firstName);$surname=trim($surname);$name=trim($firstName.' '.$surname);$username=strtolower(trim($username));$email=strtolower(trim($email));
        if(!preg_match('/^[a-z0-9_]{3,30}$/',$username)) throw new RuntimeException('Username must be 3–30 characters using letters, numbers, or underscores.');
        $check=$this->db->prepare('SELECT id FROM customers WHERE LOWER(email)=? OR LOWER(username)=? LIMIT 1');$check->execute([$email,$username]);
        if($check->fetch()) throw new RuntimeException('That username or email is already registered.');
        $token=bin2hex(random_bytes(32));$statement=$this->db->prepare('INSERT INTO customers (full_name,first_name,surname,username,email,password_hash,phone,phone_country,delivery_address,country,zip_code,email_verification_token,email_verification_expires_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,DATE_ADD(NOW(),INTERVAL 1 HOUR))');
        $statement->execute([$name,$firstName,$surname,$username,$email,password_hash($password,PASSWORD_DEFAULT),trim($phone),trim($phoneCountry),trim($address),trim($country),trim($zipCode),hash('sha256',$token)]);
        return ['id'=>(int)$this->db->lastInsertId(),'name'=>$name,'firstName'=>$firstName,'surname'=>$surname,'username'=>$username,'email'=>$email,'phone'=>trim($phone),'phoneCountry'=>trim($phoneCountry),'address'=>trim($address),'country'=>trim($country),'zipCode'=>trim($zipCode),'_verificationToken'=>$token];
    }

    public function verifyEmail(string $token): void
    {
        if(!preg_match('/^[a-f0-9]{64}$/',$token))throw new RuntimeException('The verification link is invalid.');
        $statement=$this->db->prepare('UPDATE customers SET email_verified_at=NOW(),email_verification_token=NULL,email_verification_expires_at=NULL WHERE email_verification_token=? AND email_verified_at IS NULL AND email_verification_expires_at>=NOW()');
        $statement->execute([hash('sha256',$token)]);if($statement->rowCount()!==1)throw new RuntimeException('The verification link is invalid, expired, or was already used.');
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

    private function ensureVerificationColumns(): void
    {
        $exists=$this->db->query("SHOW COLUMNS FROM customers LIKE 'email_verified_at'")->fetch();
        if(!$exists){$this->db->exec('ALTER TABLE customers ADD email_verified_at DATETIME NULL AFTER password_hash, ADD email_verification_token CHAR(64) NULL AFTER email_verified_at, ADD email_verification_expires_at DATETIME NULL AFTER email_verification_token');$this->db->exec('UPDATE customers SET email_verified_at=NOW() WHERE email_verified_at IS NULL');}
    }
}
