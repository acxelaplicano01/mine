<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequirementDiscountSeeder extends Seeder
{
    public function run()
    {
        DB::table('requirement_discounts')->truncate();
        
        DB::table('requirement_discounts')->insert([
            [
                'id' => 1,
                'name' => 'Sin requisitos mínimos',
                'description' => 'No hay requisitos mínimos para aplicar el descuento',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Cantidad mínima de compra',
                'description' => 'Requiere una cantidad mínima de productos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Monto mínimo de compra',
                'description' => 'Requiere un monto mínimo de compra',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
