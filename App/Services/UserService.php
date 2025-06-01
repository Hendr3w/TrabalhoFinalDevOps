<?php
namespace App\Services;

use App\Models\UserModel;

class UserService {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new UserModel($pdo);
    }

    public function register(string $email, string $password, string $name = '', string $lastname = ''): bool {
        if ($this->userModel->findByEmail($email)) {
            return false; 
        }

        $data = [
            'name' => $name,
            'lastname' => $lastname,
            'email' => $email,
            'password' => $password
        ];

        return $this->userModel->create($data);
    }

    public function update(int $id, array $data): bool {
        return $this->userModel->update($id, $data);
    }

    public function delete(int $id): bool {
        return $this->userModel->delete($id);
    }
}
