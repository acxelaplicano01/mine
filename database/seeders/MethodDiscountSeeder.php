<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MethodDiscountSeeder extends Seeder
{
    public function run()
    {
        DB::table('method_discounts')->truncate();
        
        DB::table('method_discounts')->insert([
            [
                'id' => 1,
                'name' => 'Código de descuento',
                'description' => 'Descuento aplicado mediante código',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Descuento automático',
                'description' => 'Descuento aplicado automáticamente',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
