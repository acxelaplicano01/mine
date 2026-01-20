<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product\OrderPurchase\OrderPurchases;
use App\Models\Distribuidor\Distribuidores;
use App\Models\Product\Products;

class GenerateOrderPurchasesDemo extends Command
{
    protected $signature = 'demo:order-purchases {--count=50 : Número de órdenes a generar}';
    protected $description = 'Genera órdenes de compra de demostración';

    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("🚀 Generando {$count} órdenes de compra de demostración...");

        // Verificar que existan distribuidores y productos
        $distributorsCount = Distribuidores::count();
        $productsCount = Products::count();

        if ($distributorsCount === 0) {
            $this->error('⚠️ No hay distribuidores en la base de datos.');
            $this->info('Crea al menos un distribuidor antes de ejecutar este comando.');
            return 1;
        }

        if ($productsCount === 0) {
            $this->error('⚠️ No hay productos en la base de datos.');
            $this->info('Crea al menos un producto antes de ejecutar este comando.');
            return 1;
        }

        $this->info("✓ Encontrados: {$distributorsCount} distribuidores y {$productsCount} productos");

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $created = 0;
        
        try {
            for ($i = 0; $i < $count; $i++) {
                OrderPurchases::factory()->create();
                $created++;
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->info("✅ Se crearon {$created} órdenes de compra exitosamente");
            
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Total de órdenes', OrderPurchases::count()],
                    ['Con guía', OrderPurchases::whereNotNull('numero_guia')->count()],
                    ['Con notas', OrderPurchases::whereNotNull('nota_al_distribuidor')->count()],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
