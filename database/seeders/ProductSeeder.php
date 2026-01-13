<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Products;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Camiseta Básica',
                'description' => 'Camiseta de algodón 100% en varios colores. Perfecta para uso diario.',
                'multimedia' => json_encode(['images/camiseta-basica.jpg']),
                'price' => 19.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pantalón Vaquero',
                'description' => 'Pantalón vaquero de corte recto, tela resistente y cómoda.',
                'multimedia' => json_encode(['images/pantalon-vaquero.jpg']),
                'price' => 59.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Zapatillas Deportivas',
                'description' => 'Zapatillas deportivas con suela antideslizante y diseño ergonómico.',
                'multimedia' => json_encode(['images/zapatillas-deportivas.jpg']),
                'price' => 69.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Chaqueta de Invierno',
                'description' => 'Chaqueta acolchada resistente al agua, ideal para climas fríos.',
                'multimedia' => json_encode(['images/chaqueta-invierno.jpg']),
                'price' => 119.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bolso de Mano',
                'description' => 'Bolso de cuero sintético con múltiples compartimentos.',
                'multimedia' => json_encode(['images/bolso-mano.jpg']),
                'price' => 45.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gafas de Sol',
                'description' => 'Gafas de sol con protección UV400 y diseño moderno.',
                'multimedia' => json_encode(['images/gafas-sol.jpg']),
                'price' => 24.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Reloj Digital',
                'description' => 'Reloj digital resistente al agua con múltiples funciones.',
                'multimedia' => json_encode(['images/reloj-digital.jpg']),
                'price' => 79.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mochila Urbana',
                'description' => 'Mochila con compartimento para laptop y diseño ergonómico.',
                'multimedia' => json_encode(['images/mochila-urbana.jpg']),
                'price' => 49.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sudadera con Capucha',
                'description' => 'Sudadera de algodón con capucha y bolsillo frontal tipo canguro.',
                'multimedia' => json_encode(['images/sudadera-capucha.jpg']),
                'price' => 34.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cinturón de Cuero',
                'description' => 'Cinturón de cuero genuino con hebilla metálica.',
                'multimedia' => json_encode(['images/cinturon-cuero.jpg']),
                'price' => 24.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gorro de Lana',
                'description' => 'Gorro tejido de lana, perfecto para el invierno.',
                'multimedia' => json_encode(['images/gorro-lana.jpg']),
                'price' => 14.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bufanda de Seda',
                'description' => 'Bufanda elegante de seda en varios colores disponibles.',
                'multimedia' => json_encode(['images/bufanda-seda.jpg']),
                'price' => 29.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Auriculares Bluetooth',
                'description' => 'Auriculares inalámbricos con cancelación de ruido.',
                'multimedia' => json_encode(['images/auriculares-bluetooth.jpg']),
                'price' => 89.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Botella Térmica',
                'description' => 'Botella de acero inoxidable que mantiene la temperatura 24h.',
                'multimedia' => json_encode(['images/botella-termica.jpg']),
                'price' => 19.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paraguas Plegable',
                'description' => 'Paraguas compacto resistente al viento.',
                'multimedia' => json_encode(['images/paraguas-plegable.jpg']),
                'price' => 12.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($products as $product) {
            Products::create($product);
        }

        $this->command->info('Se han creado ' . count($products) . ' productos de ejemplo.');
    }
}
