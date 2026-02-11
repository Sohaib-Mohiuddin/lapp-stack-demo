<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use App\ProductManager;
use App\Response;

$pm = new ProductManager();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    Response::ok($pm->getAllProducts());
    exit;
}

if ($method === 'POST') {
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
