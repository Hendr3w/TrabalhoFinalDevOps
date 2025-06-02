<?php
namespace App\Models;

use PDO;

class UserModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, lastname, email, password)
            VALUES (:name, :lastname, :email, :password)
        ");

        return $stmt->execute([
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => $data['password'] 
        ]);
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];
    
        foreach (['name', 'lastname', 'email', 'password'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
    
        if (empty($fields)) {
            return false;
        }
    
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
}
