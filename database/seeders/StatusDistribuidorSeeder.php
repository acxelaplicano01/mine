<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusDistribuidorSeeder extends Seeder
{
    public function run()
    {
        DB::table('status_distribuidores')->insert([
            [
                'name' => 'Activo',
                'description' => 'Distribuidor activo y disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inactivo',
                'description' => 'Distribuidor inactivo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Suspendido',
                'description' => 'Distribuidor suspendido temporalmente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
