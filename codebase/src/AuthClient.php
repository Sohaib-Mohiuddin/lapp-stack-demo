<?php
declare(strict_types=1);

namespace App;

final class AuthClient
{
    public static function verifyBearerOrFail(): array
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($auth, 'Bearer ')) {
            Response::json(401, ['error' => 'missing bearer token']);
            exit;
        }

        $ch = curl_init('http://auth:3000/auth/verify');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $auth,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 3
        ]);

        $raw = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($raw ?: '[]', true) ?: [];

        if ($code !== 200 || empty($data['active'])) {
            Response::json(401, ['error' => 'invalid token']);
            exit;
        }

        return $data; // contains role, sub, etc.
    }

    public static function requireRole(array $claims, array $allowedRoles): void
    {
        $role = $claims['role'] ?? '';
        if (!in_array($role, $allowedRoles, true)) {
            Response::json(403, ['error' => 'forbidden']);
            exit;
        }
    }
}
