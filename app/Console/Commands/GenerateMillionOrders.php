<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order\Orders;
use App\Models\Order\OrderItems;
use App\Models\Customer\Customers;
use App\Models\User;
use App\Models\Product\Products;
use Illuminate\Support\Facades\DB;

class GenerateMillionOrders extends Command
{
    protected $signature = 'orders:generate-million 
                            {--count=1000000 : Número de registros a generar}
                            {--batch=10000 : Tamaño de lote para inserción}
                            {--items=3 : Número promedio de items por orden}';

    protected $description = 'Genera un millón de pedidos de forma eficiente usando inserciones por lotes';

    public function handle()
    {
        $totalOrders = $this->option('count');
        $batchSize = $this->option('batch');
        $itemsPerOrder = $this->option('items');

        $this->info("🚀 Generando {$totalOrders} órdenes en lotes de {$batchSize}...");
        
        // Deshabilitar eventos para evitar la actualización de inventario
        Orders::flushEventListeners();
        OrderItems::flushEventListeners();

        // Obtener IDs existentes para referencias
        $this->info('📊 Obteniendo datos de referencia...');
        $userIds = User::pluck('id')->toArray();
        $customerIds = Customers::pluck('id')->toArray();
        $productIds = Products::pluck('id')->toArray();

        if (empty($userIds)) {
            $this->error('No hay usuarios en la base de datos. Crea al menos un usuario.');
            return 1;
        }

        if (empty($productIds)) {
            $this->error('No hay productos en la base de datos. Crea al menos un producto.');
            return 1;
        }

        $this->info("✓ Encontrados: " . count($userIds) . " usuarios, " . count($customerIds) . " clientes, " . count($productIds) . " productos");

        $batches = ceil($totalOrders / $batchSize);
        $progressBar = $this->output->createProgressBar($batches);
        $progressBar->start();

        $startTime = microtime(true);

        DB::beginTransaction();

        try {
            for ($batch = 0; $batch < $batches; $batch++) {
                $currentBatchSize = min($batchSize, $totalOrders - ($batch * $batchSize));
                
                // Generar órdenes
                $orders = [];
                $now = now();
                
                for ($i = 0; $i < $currentBatchSize; $i++) {
                    $subtotal = mt_rand(5000, 500000) / 100; // 50.00 a 5000.00
                    $total = $subtotal * 1.16; // Con impuesto

                    $orders[] = [
                        'user_id' => $userIds[array_rand($userIds)],
                        'id_customer' => !empty($customerIds) ? $customerIds[array_rand($customerIds)] : null,
                        'subtotal_price' => $subtotal,
                        'total_price' => $total,
                        'id_market' => null,
                        'id_discount' => null,
                        'id_envio' => null,
                        'id_impuesto' => null,
                        'id_moneda' => null,
                        'id_condiciones_pago' => null,
                        'fecha_emision' => now()->subDays(mt_rand(0, 730))->format('Y-m-d H:i:s'),
                        'fecha_vencimiento' => now()->addDays(mt_rand(1, 30))->format('Y-m-d H:i:s'),
                        'note' => mt_rand(0, 10) < 3 ? 'Nota generada automáticamente' : null,
                        'id_etiqueta' => null,
                        'id_status_prepared_order' => mt_rand(1, 5),
                        'id_status_order' => mt_rand(1, 6),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Insertar órdenes por lote
                DB::table('orders')->insert($orders);

                // Obtener IDs de las órdenes recién creadas
                $startOrderId = DB::table('orders')
                    ->where('created_at', $now)
                    ->min('id');

                // Generar items para las órdenes
                $orderItems = [];
                for ($i = 0; $i < $currentBatchSize; $i++) {
                    $orderId = $startOrderId + $i;
                    $itemCount = mt_rand(1, $itemsPerOrder * 2); // 1 a 6 items por orden

                    for ($j = 0; $j < $itemCount; $j++) {
                        $quantity = mt_rand(1, 10);
                        $price = mt_rand(1000, 50000) / 100; // 10.00 a 500.00
                        $subtotal = $quantity * $price;

                        $orderItems[] = [
                            'order_id' => $orderId,
                            'product_id' => $productIds[array_rand($productIds)],
                            'variant_id' => null,
                            'quantity' => $quantity,
                            'price' => $price,
                            'subtotal' => $subtotal,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        // Insertar items en sub-lotes para evitar queries muy grandes
                        if (count($orderItems) >= 5000) {
                            DB::table('order_items')->insert($orderItems);
                            $orderItems = [];
                        }
                    }
                }

                // Insertar items restantes
                if (!empty($orderItems)) {
                    DB::table('order_items')->insert($orderItems);
                }

                $progressBar->advance();
                
                // Commit cada cierto número de lotes para liberar memoria
                if (($batch + 1) % 10 === 0) {
                    DB::commit();
                    DB::beginTransaction();
                }
            }

            DB::commit();
            $progressBar->finish();
            $this->newLine(2);

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $ordersPerSecond = round($totalOrders / $duration, 2);

            $this->info("✅ ¡Completado!");
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Órdenes generadas', number_format($totalOrders)],
                    ['Tiempo total', "{$duration} segundos"],
                    ['Órdenes/segundo', number_format($ordersPerSecond)],
                    ['Items generados (aprox)', number_format($totalOrders * $itemsPerOrder)],
                ]
            );

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
