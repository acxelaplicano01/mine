<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Products;
use App\Models\Product\Inventory\Inventories;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Camiseta Básica',
                'description' => 'Camiseta de algodón 100% en varios colores. Perfecta para uso diario.',
                'sku' => 'CAMISETA-001',
                'barcode' => '1234567890120',
                'multimedia' => json_encode(['images/camiseta-basica.jpg']),
                'price_unitario' => 19.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pantalón Vaquero',
                'description' => 'Pantalón vaquero de corte recto, tela resistente y cómoda.',
                'sku' => 'PANTALON-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/pantalon-vaquero.jpg']),
                'price_unitario' => 59.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Zapatillas Deportivas',
                'description' => 'Zapatillas deportivas con suela antideslizante y diseño ergonómico.',
                'sku' => 'ZAPATILLAS-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/zapatillas-deportivas.jpg']),
                'price_unitario' => 69.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Chaqueta de Invierno',
                'description' => 'Chaqueta acolchada resistente al agua, ideal para climas fríos.',
                'sku' => 'CHAQUETA-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/chaqueta-invierno.jpg']),
                'price_unitario' => 119.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bolso de Mano',
                'description' => 'Bolso de cuero sintético con múltiples compartimentos.',
                'sku' => 'BOLSO-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/bolso-mano.jpg']),
                'price_unitario' => 45.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gafas de Sol',
                'description' => 'Gafas de sol con protección UV400 y diseño moderno.',
                'sku' => 'GAFAS-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/gafas-sol.jpg']),
                'price_unitario' => 24.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Reloj Digital',
                'description' => 'Reloj digital resistente al agua con múltiples funciones.',
                'sku' => 'RELOJ-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/reloj-digital.jpg']),
                'price_unitario' => 79.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mochila Urbana',
                'description' => 'Mochila con compartimento para laptop y diseño ergonómico.',
                'sku' => 'MOCHILA-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/mochila-urbana.jpg']),
                'price_unitario' => 49.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sudadera con Capucha',
                'description' => 'Sudadera de algodón con capucha y bolsillo frontal tipo canguro.',
                'sku' => 'SUDADERA-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/sudadera-capucha.jpg']),
                'price_unitario' => 34.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cinturón de Cuero',
                'description' => 'Cinturón de cuero genuino con hebilla metálica.',
                'sku' => 'CINTURON-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/cinturon-cuero.jpg']),
                'price_unitario' => 24.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gorro de Lana',
                'description' => 'Gorro tejido de lana, perfecto para el invierno.',
                'sku' => 'GORRO-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/gorro-lana.jpg']),
                'price_unitario' => 14.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bufanda de Seda',
                'description' => 'Bufanda elegante de seda en varios colores disponibles.',
                'sku' => 'BUFANDA-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/bufanda-seda.jpg']),
                'price_unitario' => 29.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Auriculares Bluetooth',
                'description' => 'Auriculares inalámbricos con cancelación de ruido.',
                'sku' => 'AURICULAR-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/auriculares-bluetooth.jpg']),
                'price_unitario' => 89.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Botella Térmica',
                'description' => 'Botella de acero inoxidable que mantiene la temperatura 24h.',
                'sku' => 'BOTELLA-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/botella-termica.jpg']),
                'price_unitario' => 19.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paraguas Plegable',
                'description' => 'Paraguas compacto resistente al viento.',
                'sku' => 'PARAGUAS-001',
                'barcode' => '1234567890123',
                'multimedia' => json_encode(['images/paraguas-plegable.jpg']),
                'price_unitario' => 12.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($products as $productData) {
            // Crear el producto
            $product = Products::create($productData);
            
            // Crear inventario para el producto
            Inventories::create([
                'cantidad_inventario' => rand(10, 100), // Cantidad aleatoria entre 10 y 100
                'umbral_aviso_inventario' => rand(5, 15), // Umbral aleatorio entre 5 y 15
                'seguimiento_inventario' => rand(0, 1) == 1, // Aleatorio true/false
                'location' => 'Almacén Principal',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Actualizar el producto con el id_inventory
            $product->update(['id_inventory' => Inventories::latest('id')->first()->id]);
        }

        $this->command->info('Se han creado ' . count($products) . ' productos con sus inventarios.');
    }
}

