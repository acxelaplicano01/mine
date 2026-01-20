<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product\Transfer\Transfers;
use App\Models\Product\Products;
use App\Models\Product\Transfer\StatusTransfer;
use App\Models\PointSale\Branch\Branches;

class GenerateTransfersDemo extends Command
{
    protected $signature = 'demo:transfers {--count=50 : Número de transferencias a generar}';
    protected $description = 'Genera transferencias de demostración';

    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("🚀 Generando {$count} transferencias de demostración...");

        // Verificar que existan productos
        $productsCount = Products::count();

        if ($productsCount === 0) {
            $this->error('⚠️ No hay productos en la base de datos.');
            $this->info('Crea al menos un producto antes de ejecutar este comando.');
            return 1;
        }

        // Verificar que existan sucursales
        $sucursalesCount = Branches::count();

        if ($sucursalesCount === 0) {
            $this->error('⚠️ No hay sucursales (branches) en la base de datos.');
            $this->info('Crea sucursales desde el sistema primero.');
            return 1;
        }

        $this->info("✓ Encontrados: {$productsCount} productos y {$sucursalesCount} sucursales");

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $created = 0;
        
        try {
            for ($i = 0; $i < $count; $i++) {
                Transfers::factory()->create();
                $created++;
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->info("✅ Se crearon {$created} transferencias exitosamente");
            
            $statuses = StatusTransfer::all();
            $statusData = [];
            foreach ($statuses as $status) {
                $statusData[] = [
                    $status->name,
                    Transfers::where('id_status_transfer', $status->id)->count()
                ];
            }
            
            $this->table(
                ['Estado', 'Cantidad'],
                $statusData
            );

            $this->newLine();
            $this->info('Total de transferencias: ' . Transfers::count());

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
