<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusTransportistaSeeder extends Seeder
{
    public function run()
    {
        DB::table('status_transportistas')->insert([
            [
                'name' => 'Activo',
                'description' => 'Transportista activo y disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inactivo',
                'description' => 'Transportista inactivo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Suspendido',
                'description' => 'Transportista suspendido temporalmente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
