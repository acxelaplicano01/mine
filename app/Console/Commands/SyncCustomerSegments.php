<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CustomerSegmentService;

class SyncCustomerSegments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'segments:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar segmentos automáticos de clientes basados en su comportamiento de compra';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sincronizando segmentos automáticos de clientes...');
        
        try {
            CustomerSegmentService::syncAllAutomaticSegments();
            $this->info('✓ Segmentos sincronizados exitosamente.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error al sincronizar segmentos: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
