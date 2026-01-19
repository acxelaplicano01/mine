<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Products;
use App\Models\Product\VariantProduct;

class VariantProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener algunos productos para agregarles variantes
        $products = Products::limit(5)->get();

        if ($products->isEmpty()) {
            $this->command->warn('No hay productos disponibles. Ejecuta primero ProductSeeder.');
            return;
        }

        // Variantes de color
        $colores = [
            ['nombre' => 'Rojo', 'precio' => 25.00],
            ['nombre' => 'Azul', 'precio' => 25.00],
            ['nombre' => 'Verde', 'precio' => 27.00],
            ['nombre' => 'Negro', 'precio' => 30.00],
            ['nombre' => 'Blanco', 'precio' => 25.00],
        ];

        // Variantes de talla
        $tallas = [
            ['nombre' => 'S', 'precio' => 20.00],
            ['nombre' => 'M', 'precio' => 22.00],
            ['nombre' => 'L', 'precio' => 25.00],
            ['nombre' => 'XL', 'precio' => 28.00],
        ];

        // Variantes de estilo
        $estilos = [
            ['nombre' => 'Geometrico', 'precio' => 25.00],
            ['nombre' => 'Bronce', 'precio' => 25.00],
            ['nombre' => 'Clasico', 'precio' => 23.00],
            ['nombre' => 'Moderno', 'precio' => 28.00],
        ];

        foreach ($products as $index => $product) {
            // Determinar qué tipo de variante agregar según el índice
            $variantType = $index % 3;
            
            // SKU base del producto
            $baseSku = $product->sku ?? 'PROD-' . $product->id;
            $counter = 1;
            
            if ($variantType === 0) {
                // Agregar variantes de color
                foreach ($colores as $color) {
                    $variantSku = $baseSku . '-' . strtoupper(substr($color['nombre'], 0, 3)) . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT);
                    VariantProduct::create([
                        'product_id' => $product->id,
                        'sku' => $variantSku,
                        'barcode' => '789' . rand(1000000000, 9999999999),
                        'price' => $color['precio'],
                        'cantidad_inventario' => rand(10, 150),
                        'weight' => rand(100, 500) / 100,
                        'name_variant' => 'Color',
                        'valores_variante' => json_encode(['color' => $color['nombre']]),
                    ]);
                    $counter++;
                }
            } elseif ($variantType === 1) {
                // Agregar variantes de talla
                foreach ($tallas as $talla) {
                    $variantSku = $baseSku . '-' . $talla['nombre'] . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT);
                    VariantProduct::create([
                        'product_id' => $product->id,
                        'sku' => $variantSku,
                        'barcode' => '789' . rand(1000000000, 9999999999),
                        'price' => $talla['precio'],
                        'cantidad_inventario' => rand(15, 100),
                        'weight' => rand(100, 300) / 100,
                        'name_variant' => 'Talla',
                        'valores_variante' => json_encode(['talla' => $talla['nombre']]),
                    ]);
                    $counter++;
                }
            } else {
                // Agregar variantes de estilo
                foreach ($estilos as $estilo) {
                    $variantSku = $baseSku . '-' . strtoupper(substr($estilo['nombre'], 0, 3)) . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT);
                    VariantProduct::create([
                        'product_id' => $product->id,
                        'sku' => $variantSku,
                        'barcode' => '789' . rand(1000000000, 9999999999),
                        'price' => $estilo['precio'],
                        'cantidad_inventario' => rand(20, 120),
                        'weight' => rand(150, 400) / 100,
                        'name_variant' => 'Estilo',
                        'valores_variante' => json_encode(['estilo' => $estilo['nombre']]),
                    ]);
                    $counter++;
                }
            }
        }

        $this->command->info('Variantes de productos creadas exitosamente.');
    }
}
