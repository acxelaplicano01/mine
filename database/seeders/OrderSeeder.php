<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order\Orders;
use App\Models\Order\OrderItems;
use App\Models\Product\Products;
use App\Models\Customer\Customers;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener datos necesarios
        $users = User::all();
        $products = Products::with('variants')->get();
        $customers = Customers::all();

        if ($users->isEmpty() || $products->isEmpty() || $customers->isEmpty()) {
            $this->command->warn('Asegúrate de ejecutar primero UserSeeder, ProductSeeder y CustomerSeeder');
            return;
        }

        // Crear 15 pedidos de ejemplo
        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();
            $user = $users->random();
            
            // Número aleatorio de productos por pedido (1-5)
            $numProducts = rand(1, 5);
            $orderProducts = $products->random($numProducts);
            
            $subtotal = 0;
            $items = [];
            
            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                
                // 50% de probabilidad de usar variante si existe
                $variant = null;
                if ($product->variants->isNotEmpty() && rand(0, 1)) {
                    $variant = $product->variants->random();
                    $price = $variant->price;
                } else {
                    $price = $product->price;
                }
                
                $itemSubtotal = $price * $quantity;
                $subtotal += $itemSubtotal;
                
                $items[] = [
                    'product_id' => $product->id,
                    'variant_id' => $variant ? $variant->id : null,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $itemSubtotal,
                ];
            }
            
            $tax = $subtotal * 0.12;
            $total = $subtotal + $tax;
            
            // Crear el pedido
            $order = Orders::create([
                'user_id' => $user->id,
                'id_customer' => $customer->id,
                'subtotal_price' => $subtotal,
                'total_price' => $total,
                'note' => rand(0, 1) ? null : 'Nota del pedido ' . ($i + 1),
                'id_status_order' => rand(1, 3),
                'id_status_prepared_order' => rand(1, 2),
                'id_condiciones_pago' => rand(1, 4),
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]);
            
            // Crear los items del pedido
            foreach ($items as $item) {
                OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
        }

        $this->command->info('Pedidos creados exitosamente con sus items.');
    }
}

        