<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeDiscountSeeder extends Seeder
{
    public function run()
    {
        DB::table('type_discounts')->truncate();
        
        DB::table('type_discounts')->insert([
            [
                'id' => 1,
                'name' => 'Descuento en productos',
                'description' => 'Aplica descuentos a productos específicos o colecciones de productos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Buy X Get Y',
                'description' => 'Aplica descuentos a productos específicos o colecciones de productos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Descuento en el pedido',
                'description' => 'Aplica descuentos al importe total del pedido',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Envío gratis',
                'description' => 'Ofrece envío gratis en un pedido',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
