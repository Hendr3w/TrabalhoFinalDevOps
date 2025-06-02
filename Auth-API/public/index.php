<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\UserController;
use App\Controllers\AuthController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = str_replace(['/index.php', '/public'], '', $uri);


switch ("$method $uri") {
    case 'POST /user':
        UserController::register();
        break;
    case 'POST /token':
        AuthController::login();
        break;
    case 'GET /token':
        AuthController::verify();
        break;
    case 'GET /user':
        UserController::getByEmail();
        break;
    case 'GET /user/profile':
        UserController::getProfile();
        break;
    default:
        if (preg_match('#^/user/(\d+)$#', $uri, $matches)) {
            $id = $matches[1];
            if ($method === 'PUT') {
                UserController::update($id);
            } elseif ($method === 'DELETE') {
                UserController::delete($id);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método não permitido']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Rota não encontrada']);
        }
}
