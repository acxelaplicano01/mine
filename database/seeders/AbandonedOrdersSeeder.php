<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order\AbandonedOrders;
use App\Models\User;
use App\Models\Product\Products;
use App\Models\Market\Markets;
use Carbon\Carbon;

class AbandonedOrdersSeeder extends Seeder
{
    public function run()
    {
        // Obtener usuarios, productos y markets existentes
        $users = User::all();
        $products = Products::all();
        $markets = Markets::all();

        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No hay usuarios o productos en la base de datos. Por favor, crea algunos primero.');
            return;
        }

        // Crear 5 carritos abandonados de ejemplo
        $abandonedOrders = [
            [
                'user_id' => $users->random()->id,
                'product_id' => $products->random()->id,
                'quantity' => rand(1, 5),
                'total_price' => rand(50, 500),
                'id_market' => $markets->isNotEmpty() ? $markets->random()->id : null,
                'note' => 'Cliente abandonó el carrito después de revisar el precio',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $users->random()->id,
                'product_id' => $products->random()->id,
                'quantity' => rand(1, 3),
                'total_price' => rand(100, 800),
                'id_market' => $markets->isNotEmpty() ? $markets->random()->id : null,
                'note' => 'Email de recuperación enviado',
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $users->random()->id,
                'product_id' => $products->random()->id,
                'quantity' => rand(2, 4),
                'total_price' => rand(200, 1000),
                'id_market' => $markets->isNotEmpty() ? $markets->random()->id : null,
                'note' => 'Posible problema con el método de pago',
                'created_at' => Carbon::now()->subHours(12),
            ],
            [
                'user_id' => $users->random()->id,
                'product_id' => $products->random()->id,
                'quantity' => rand(1, 6),
                'total_price' => rand(150, 600),
                'id_market' => $markets->isNotEmpty() ? $markets->random()->id : null,
                'note' => 'Carrito abandonado',
                'created_at' => Carbon::now()->subWeek(),
            ],
            [
                'user_id' => $users->random()->id,
                'product_id' => $products->random()->id,
                'quantity' => rand(1, 2),
                'total_price' => rand(80, 400),
                'id_market' => $markets->isNotEmpty() ? $markets->random()->id : null,
                'note' => 'Cliente agregó producto pero no completó la información de envío',
                'created_at' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($abandonedOrders as $orderData) {
            AbandonedOrders::create($orderData);
        }

        $this->command->info('✅ Se crearon 5 carritos abandonados de ejemplo.');
    }
}
