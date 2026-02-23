<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'server error']);
});

require __DIR__ . '/../../vendor/autoload.php';

use App\ProductManager;
use App\Response;
use App\AuthClient;

// Always validate token first and capture claims
$claims = AuthClient::verifyBearerOrFail();

$pm = new ProductManager();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    AuthClient::requireRole($claims, ['staff','admin']);
    Response::ok($pm->getAllProducts());
    exit;
}

if ($method === 'POST') {
    AuthClient::requireRole($claims, ['admin']);
    $data = json_decode(file_get_contents("php://input") ?: "[]", true) ?: [];

    $id = (int)($data['product_no'] ?? 0);
    $name = trim((string)($data['name'] ?? ''));
    $price = $data['price'] ?? null;

    if ($id <= 0 || $name === '' || !is_numeric($price)) {
        Response::badRequest("product_no, name, price required");
        exit;
    }

    $pm->createProduct($id, $name, (float)$price);
    Response::created(["created" => true, "product_no" => $id]);
    exit;
}

Response::badRequest();
