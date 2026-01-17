<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImpuestoSeeder extends Seeder
{
    public function run()
    {
        DB::table('impuestos')->truncate();
        
        DB::table('impuestos')->insert([
            [
                'id' => 1,
                'nombre_impuesto' => 'IVA',
                'porcentaje_impuesto' => 12.00,
                'descripcion_impuesto' => 'Impuesto al Valor Agregado del 12%',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nombre_impuesto' => 'ISV',
                'porcentaje_impuesto' => 15.00,
                'descripcion_impuesto' => 'Impuesto Sobre Ventas del 15%',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
