<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Config\database;

class AuthController {
    public static function login() {
        $pdo = (new database())->connect();
        $service = new AuthService($pdo);

        $input = json_decode(file_get_contents('php://input'), true);

        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        $token = $service->login($email, $password);

        if (!$token) {
            http_response_code(401);
            echo json_encode(['token' => false]);
            return;
        }

        echo json_encode(['token' => $token]);
    }

    public static function verify() {
        $pdo = (new database())->connect();
        $service = new AuthService($pdo);
    
        $userId = $_GET['user'] ?? '';
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);
    
        if (!$token || !$userId) {
            http_response_code(401);
            echo json_encode(['auth' => false]);
            return;
        }
    
        $valid = $service->verifyToken($userId, $token);
    
        echo json_encode(['auth' => $valid]);
    }
    
}
