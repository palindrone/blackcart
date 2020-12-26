<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Exception;
use PHPUnit\TextUI\XmlConfiguration\PHPUnit;
use Tests\TestCase;

class APITest extends TestCase
{

    private static $_objectStructure = [
        "*" => [
            "id",
            "name",
            "options",
            "variants",
            "weight",
            "price"
        ]
    ];

    public function testNoStore() {
        $response = $this->get('/api/stores/0/products');

        $response->assertStatus(404);
    }

    public function testShopify() {
        $response = $this->get('/api/stores/1/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "status",
            "products" => self::$_objectStructure
        ]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testWoocommerce()
    {
        $response = $this->get('/api/stores/2/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "status",
            "products" => self::$_objectStructure
        ]);
    }
}
