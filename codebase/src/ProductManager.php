<?php
declare(strict_types=1);

namespace App;

use PDO;

final class ProductManager
{
    private PDO $db;

    public function __construct(?PDO $pdo = null)
    {
        $this->db = $pdo ?? new PDO(
            'pgsql:host=' . getenv('DB_HOST') .
            ';port=' . (getenv('DB_PORT') ?: '5432') .
            ';dbname=' . getenv('DB_NAME'),
            getenv('DB_USER'),
            getenv('DB_PASS'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS products (
              product_no INTEGER PRIMARY KEY,
              name TEXT NOT NULL,
              price NUMERIC(10,2) NOT NULL
            );
        ");
    }

    // Test helper: clean slate
    public function resetForTests(): void
    {
        $this->db->exec("DROP TABLE IF EXISTS products;");
        $this->ensureTableExists();
    }

    public function createProduct(int $id, string $name, float $price): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO products (product_no, name, price) VALUES (?, ?, ?)"
        );
        $stmt->execute([$id, $name, $price]);
    }

    public function getProduct(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT product_no, name, price FROM products WHERE product_no = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllProducts(): array
    {
        return $this->db->query("SELECT product_no, name, price FROM products ORDER BY product_no")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProduct(int $id, string $name, float $price): bool
    {
        $stmt = $this->db->prepare("UPDATE products SET name = ?, price = ? WHERE product_no = ?");
        $stmt->execute([$name, $price, $id]);
        return $stmt->rowCount() === 1;
    }

    public function deleteProduct(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM products WHERE product_no = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() === 1;
    }
}
