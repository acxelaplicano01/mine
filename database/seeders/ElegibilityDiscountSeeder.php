<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ElegibilityDiscountSeeder extends Seeder
{
    public function run()
    {
        DB::table('elegibility_discounts')->truncate();
        
        DB::table('elegibility_discounts')->insert([
            [
                'id' => 1,
                'name' => 'Todos los clientes',
                'description' => 'Descuento disponible para todos los clientes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Clientes específicos',
                'description' => 'Descuento disponible solo para clientes específicos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Segmentos de clientes',
                'description' => 'Descuento disponible para segmentos específicos de clientes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
