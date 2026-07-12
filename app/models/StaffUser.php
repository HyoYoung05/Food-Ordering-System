<?php
declare(strict_types=1);

final class StaffUser
{
    public function __construct(private PDO $db) {}

    public function authenticate(string $email, string $password): ?array
    {
        $this->ensureRoleColumn();
        $statement = $this->db->prepare('SELECT id, full_name, email, password_hash, role FROM staff_users WHERE email = ? AND is_active = 1');
        $statement->execute([strtolower(trim($email))]);
        $user = $statement->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) return null;
        return ['id'=>(int)$user['id'], 'name'=>$user['full_name'], 'email'=>$user['email'], 'role'=>$user['role'] ?: 'staff'];
    }

    public function all(): array
    {
        $this->ensureRoleColumn();
        $rows = $this->db->query('SELECT id, full_name, email, role, is_active, created_at, updated_at FROM staff_users ORDER BY full_name')->fetchAll();
        return array_map(fn($row) => [
            'id' => (int)$row['id'],
            'name' => $row['full_name'],
            'email' => $row['email'],
            'role' => $row['role'] ?: 'staff',
            'isActive' => (bool)$row['is_active'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ], $rows);
    }

    public function save(array $data): array
    {
        $this->ensureRoleColumn();
        $id = (int)($data['id'] ?? 0);
        $name = trim((string)($data['name'] ?? ''));
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');
        $role = strtolower(trim((string)($data['role'] ?? 'staff')));
        $isActive = filter_var($data['isActive'] ?? true, FILTER_VALIDATE_BOOLEAN);
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Enter a valid staff name and email.');
        if (!in_array($role, ['staff', 'manager'], true)) throw new RuntimeException('Choose Staff or Manager as the role.');
        if ($id <= 0 && strlen($password) < 6) throw new RuntimeException('New staff accounts need a password with at least 6 characters.');
        if ($id > 0 && $password !== '' && strlen($password) < 6) throw new RuntimeException('The new password must contain at least 6 characters.');

        if ($id > 0) {
            if ($password !== '') {
                $statement = $this->db->prepare('UPDATE staff_users SET full_name=?, email=?, role=?, is_active=?, password_hash=? WHERE id=?');
                $statement->execute([$name, $email, $role, $isActive ? 1 : 0, password_hash($password, PASSWORD_DEFAULT), $id]);
            } else {
                $statement = $this->db->prepare('UPDATE staff_users SET full_name=?, email=?, role=?, is_active=? WHERE id=?');
                $statement->execute([$name, $email, $role, $isActive ? 1 : 0, $id]);
            }
            if ($statement->rowCount() < 1 && !$this->find($id)) throw new RuntimeException('Staff account not found.');
        } else {
            $statement = $this->db->prepare('INSERT INTO staff_users (full_name, email, password_hash, role, is_active) VALUES (?,?,?,?,?)');
            $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $isActive ? 1 : 0]);
            $id = (int)$this->db->lastInsertId();
        }
        return $this->find($id);
    }

    public function setStatus(int $id, bool $isActive): array
    {
        $this->ensureRoleColumn();
        if ($id <= 0) throw new RuntimeException('Choose a staff account first.');
        $statement = $this->db->prepare('UPDATE staff_users SET is_active=? WHERE id=?');
        $statement->execute([$isActive ? 1 : 0, $id]);
        return $this->find($id);
    }

    private function find(int $id): array
    {
        $statement = $this->db->prepare('SELECT id, full_name, email, role, is_active, created_at, updated_at FROM staff_users WHERE id=?');
        $statement->execute([$id]);
        $row = $statement->fetch();
        if (!$row) throw new RuntimeException('Staff account not found.');
        return [
            'id' => (int)$row['id'],
            'name' => $row['full_name'],
            'email' => $row['email'],
            'role' => $row['role'] ?: 'staff',
            'isActive' => (bool)$row['is_active'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
    }

    private function ensureRoleColumn(): void
    {
        if (!$this->db->query("SHOW COLUMNS FROM staff_users LIKE 'role'")->fetch()) {
            $this->db->exec("ALTER TABLE staff_users ADD COLUMN role ENUM('staff','manager') NOT NULL DEFAULT 'staff' AFTER password_hash");
        }
    }
}
