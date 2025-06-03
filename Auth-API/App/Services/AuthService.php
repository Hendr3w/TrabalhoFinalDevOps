<?php
namespace App\Services;

use App\Models\UserModel;
use App\Helpers\JWT;
use App\Config\RedisCLient;

class AuthService {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new UserModel($pdo);
    }

    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);

        if (!$user || $user['password'] !== $password) {
            return false;
        }

        $payload = [
            'sub' => $user['id'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + 3600
        ];

        return JWT::encode($payload);
    }

    public function verifyToken(string $userId, string $token): bool {
        $user = $this->userModel->findById($userId);
        //var_dump($user); Usamos para debug.

        if (!$user) {
            return false;
        }
    
        $decoded = JWT::decode($token);
        //var_dump($decoded);
    
        if (!$decoded) {
            return false;
        }
    
        return ($decoded['sub'] == $user['id']);
    }
    
    
}
