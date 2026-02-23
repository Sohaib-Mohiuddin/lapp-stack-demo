<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'server error']);
});

require __DIR__ . '/../../../vendor/autoload.php';

use App\ProductManager;
use App\Response;
use App\AuthClient;

$claims = AuthClient::verifyBearerOrFail();
$pm = new ProductManager();

$uri = $_SERVER['REQUEST_URI'] ?? '';
$parts = explode('/', trim($uri, '/'));
$id = (int)end($parts);

if ($id <= 0) {
    Response::badRequest("Invalid product id");
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    AuthClient::requireRole($claims, ['staff','admin']);
    $p = $pm->getProduct($id);
    if (!$p) { Response::notFound(); exit; }
    Response::ok($p);
    exit;
}

if ($method === 'PUT') {
    AuthClient::requireRole($claims, ['admin']);
    if (!$pm->getProduct($id)) { Response::notFound(); exit; }

    $data = json_decode(file_get_contents("php://input") ?: "[]", true) ?: [];
    $name = trim((string)($data['name'] ?? ''));
    $price = $data['price'] ?? null;

    if ($name === '' || !is_numeric($price)) {
        Response::badRequest("name and price required");
        exit;
    }

    $ok = $pm->updateProduct($id, $name, (float)$price);
    if (!$ok) { Response::notFound(); exit; }

    Response::ok(["updated" => true, "product_no" => $id]);
    exit;
}

if ($method === 'DELETE') {
    AuthClient::requireRole($claims, ['admin']);
    $ok = $pm->deleteProduct($id);
    if (!$ok) { Response::notFound(); exit; }

    Response::ok(["deleted" => true, "product_no" => $id]);
    exit;
}

Response::badRequest();
