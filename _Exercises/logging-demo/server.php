<?php
header('Content-Type: application/json');

// Generate a unique ID per request
$requestId = uniqid("req-", true);

// Simple logger function
function logEvent($level, $event, $data, $requestId) {
    $log = [
        "timestamp" => gmdate("c"),
        "level" => $level,
        "event" => $event,
        "requestId" => $requestId,
    ] + $data;

    file_put_contents('logs.txt', json_encode($log) . PHP_EOL, FILE_APPEND);
}

// ROUTES
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// 1. Operational example
if ($path === '/hello' && $method === 'GET') {

    logEvent("info", "operational_hello",
        ["message" => "Normal GET /hello"], $requestId);

    echo json_encode(["status" => "ok", "message" => "Hello world"]);
    exit;
}

// 2. Audit example
if ($path === '/secure' && $method === 'GET') {

    $token = $_GET['token'] ?? null;
    $allowed = ($token === "secret123");

    logEvent("info", "audit_access",
        ["allowed" => $allowed], $requestId);

    if (!$allowed) {
        echo json_encode(["error" => "Access denied"]);
        exit;
    }

    echo json_encode(["secureData" => "Here is your protected content"]);
    exit;
}

// 3. Error example
if ($path === '/fail') {
    try {
        throw new Exception("Simulated failure for demo");
    } catch (Exception $e) {
        logEvent("error", "exception", [
            "message" => $e->getMessage()
        ], $requestId);

        http_response_code(500);
        echo json_encode(["error" => "Something went wrong"]);
        exit;
    }
}

// 4. Multi-log demo: operational + audit + error in one request
if ($path === '/demo' && $method === 'GET') {

    // 1. Operational log (normal event)
    logEvent("info", "operational_demo", [
        "details" => "Demo request started"
    ], $requestId);

    // 2. Audit log (pretend access check)
    $token = $_GET['token'] ?? null;
    $allowed = ($token === "demo123");

    logEvent("info", "audit_demo", [
        "allowed" => $allowed
    ], $requestId);

    // 3. Error log (forced failure)
    try {
        throw new Exception("Simulated demo exception");
    } catch (Exception $e) {
        logEvent("error", "demo_exception", [
            "message" => $e->getMessage()
        ], $requestId);
    }

    echo json_encode([
        "status" => "ok",
        "info" => "This endpoint generates ALL log types in one request",
        "requestId" => $requestId
    ]);

    exit;
}

echo json_encode(["error" => "Unknown route"]);