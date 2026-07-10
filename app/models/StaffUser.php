<?php
declare(strict_types=1);

final class StaffUser
{
    public function __construct(private PDO $db) {}

    public function authenticate(string $email, string $password): ?array
    {
        $statement = $this->db->prepare('SELECT id, full_name, email, password_hash FROM staff_users WHERE email = ? AND is_active = 1');
        $statement->execute([strtolower(trim($email))]);
        $user = $statement->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) return null;
        return ['id'=>(int)$user['id'], 'name'=>$user['full_name'], 'email'=>$user['email'], 'role'=>'staff'];
    }
}
