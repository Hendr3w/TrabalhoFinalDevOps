<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Controllers\AuthController;
use App\Controllers\UserController;

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$path = rtrim($path, '/');

switch ($path) {
    case '/token':
        if ($method === 'POST') {
            AuthController::login();
        } elseif ($method === 'GET') {
            AuthController::verify();
        }
        break;

    case '/user':
        if ($method === 'POST') {
            UserController::register();
        } elseif ($method === 'GET') {
            UserController::getByEmail();
        }
        break;

    default:
        if (preg_match('#^/user/(\d+)$#', $path, $matches)) {
            $userId = (int)$matches[1];

            if ($method === 'PUT') {
                UserController::update($userId);
            } elseif ($method === 'DELETE') {
                UserController::delete($userId);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método não permitido.']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Rota não encontrada.']);
        }
}
