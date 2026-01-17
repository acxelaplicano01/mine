<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusPreparedOrderSeeder extends Seeder
{
    public function run()
    {
        DB::table('status_prepared_orders')->truncate();
        
        DB::table('status_prepared_orders')->insert([
            [
                'id' => 1,
                'name' => 'Sin preparar',
                'description' => 'El pedido no ha sido preparado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'En preparación',
                'description' => 'El pedido está siendo preparado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Preparado',
                'description' => 'El pedido ha sido preparado completamente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Enviado',
                'description' => 'El pedido ha sido enviado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Entregado',
                'description' => 'El pedido ha sido entregado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
