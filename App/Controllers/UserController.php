<?php
namespace App\Controllers;

use App\Services\UserService;
use App\Config\database;
use App\Helpers\JWT;

class UserController {
    public static function register() {
        $pdo = (new database())->connect();
        $service = new UserService($pdo);

        $input = json_decode(file_get_contents('php://input'), true);

        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode(['error' => 'Email e senha são obrigatórios.']);
            return;
        }

        $success = $service->register($email, $password);

        if (!$success) {
            http_response_code(409);
            echo json_encode(['error' => 'Usuário já existe.']);
            return;
        }

        http_response_code(201);
        echo json_encode(['message' => 'Usuário registrado com sucesso.']);
    }

    public static function getProfile() {
        $headers = apache_request_headers();
        $authHeader = $headers['Authorization'] ?? '';

        if (!str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['error' => 'Token ausente ou inválido.']);
            return;
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $payload = JWT::decode($token);

        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido.']);
            return;
        }

        echo json_encode([
            'userId' => $payload->userId,
            'email' => $payload->email
        ]);
    }

    public static function update($id) {
        $pdo = (new Database())->connect();
        $service = new UserService($pdo);

        $input = json_decode(file_get_contents('php://input'), true);

        $success = $service->update((int)$id, $input);

        http_response_code($success ? 200 : 400);
        echo json_encode(['success' => $success]);
    }

    public static function delete($id) {
        $pdo = (new Database())->connect();
        $service = new UserService($pdo);

        $success = $service->delete((int)$id);

        http_response_code($success ? 200 : 404);
        echo json_encode(['success' => $success]);
    }

    public static function getByEmail() {
        $pdo = (new Database())->connect();
        $service = new UserService($pdo);
    
        $redis = RedisClient::connect();
    
        $email = $_GET['email'] ?? '';
        if (!$email) {
            http_response_code(400);
            echo json_encode(['error' => 'Email não informado']);
            return;
        }
    
        $cacheKey = "user:$email";
        if ($redis->exists($cacheKey)) {
            echo $redis->get($cacheKey);
            return;
        }
    
        $user = $service->getByEmail($email);
    
        if (!$user) {
            http_response_code(404);
            echo json_encode(['message' => 'User not found']);
            return;
        }
    
        $response = json_encode($user);
    
        $redis->setex($cacheKey, 300, $response);
    
        echo $response;
    }
}
