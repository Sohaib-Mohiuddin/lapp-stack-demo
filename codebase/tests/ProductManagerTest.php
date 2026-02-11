<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\ProductManager;

final class ProductManagerTest extends TestCase
{
    private ProductManager $pm;

    protected function setUp(): void
    {
        $this->pm = new ProductManager();
        $this->pm->resetForTests();
    }

    public function testCreateAndReadProduct(): void
    {
        $this->pm->createProduct(1, 'Cheese', 9.99);

        $p = $this->pm->getProduct(1);
        $this->assertIsArray($p);
        $this->assertSame('Cheese', $p['name']);
        $this->assertEquals(9.99, (float)$p['price']);
    }

    public function testGetAllProducts(): void
    {
        $this->pm->createProduct(1, 'Cheese', 9.99);
        $this->pm->createProduct(2, 'Milk', 4.25);

        $all = $this->pm->getAllProducts();
        $this->assertCount(2, $all);
    }

    public function testUpdateProduct(): void
    {
        $this->pm->createProduct(1, 'Cheese', 9.99);

        $ok = $this->pm->updateProduct(1, 'Cheddar', 10.50);
        $this->assertTrue($ok);

        $p = $this->pm->getProduct(1);
        $this->assertSame('Cheddar', $p['name']);
        $this->assertEquals(10.50, (float)$p['price']);
    }

    public function testDeleteProduct(): void
    {
        $this->pm->createProduct(1, 'Cheese', 9.99);

        $ok = $this->pm->deleteProduct(1);
        $this->assertTrue($ok);

        $p = $this->pm->getProduct(1);
        $this->assertFalse($p);
    }

    public function testMissingProductReturnsFalse(): void
    {
        $p = $this->pm->getProduct(999);
        $this->assertFalse($p);
    }
}
