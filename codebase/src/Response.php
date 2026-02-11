<?php
declare(strict_types=1);

namespace App;

final class Response
{
    public static function json(int $status, mixed $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function ok(mixed $payload): void { self::json(200, $payload); }
    public static function created(mixed $payload): void { self::json(201, $payload); }
    public static function badRequest(string $msg = "Bad request"): void { self::json(400, ["error" => $msg]); }
    public static function notFound(string $msg = "Not found"): void { self::json(404, ["error" => $msg]); }
}
