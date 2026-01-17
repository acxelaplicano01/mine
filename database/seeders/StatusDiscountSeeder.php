<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusDiscountSeeder extends Seeder
{
    public function run()
    {
        DB::table('status_discounts')->truncate();
        
        DB::table('status_discounts')->insert([
            [
                'id' => 1,
                'name' => 'Activo',
                'description' => 'El descuento está activo y disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Inactivo',
                'description' => 'El descuento está inactivo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Programado',
                'description' => 'El descuento está programado para activarse',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Expirado',
                'description' => 'El descuento ha expirado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
