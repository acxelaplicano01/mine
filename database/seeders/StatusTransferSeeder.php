<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Transfer\StatusTransfer;

class StatusTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'id' => 1,
                'name' => 'Pendiente',
                'nombre_status' => 'Pendiente',
                'descripcion_status' => 'Transferencia pendiente de envío',
            ],
            [
                'id' => 2,
                'name' => 'En tránsito',
                'nombre_status' => 'En tránsito',
                'descripcion_status' => 'Transferencia en camino al destino',
            ],
            [
                'id' => 3,
                'name' => 'Completado',
                'nombre_status' => 'Completado',
                'descripcion_status' => 'Transferencia recibida y completada',
            ],
            [
                'id' => 4,
                'name' => 'Cancelado',
                'nombre_status' => 'Cancelado',
                'descripcion_status' => 'Transferencia cancelada',
            ],
        ];

        foreach ($statuses as $status) {
            StatusTransfer::updateOrCreate(
                ['id' => $status['id']],
                $status
            );
        }

        $this->command->info('✅ Se crearon ' . StatusTransfer::count() . ' estados de transferencia');
    }
}
