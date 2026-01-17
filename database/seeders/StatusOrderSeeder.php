<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusOrderSeeder extends Seeder
{
    public function run()
    {
        DB::table('status_orders')->truncate();
        
        DB::table('status_orders')->insert([
            [
                'id' => 1,
                'name' => 'Pagado',
                'description' => 'El pedido ha sido pagado completamente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Pendiente de pago',
                'description' => 'El pedido está pendiente de pago',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Cancelado',
                'description' => 'El pedido ha sido cancelado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Reembolsado',
                'description' => 'El pedido ha sido reembolsado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
