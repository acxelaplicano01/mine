<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaisSeeder extends Seeder
{
    public function run()
    {
        DB::table('paises')->insert([
            [
                'nombre' => 'Honduras',
                'codigo_iso2' => 'HN',
                'codigo_numerico' => '340',
                'prefijo_telefono' => '+504',
                'id_moneda' => 1, // Lempira
                'region' => 'América',
                'subregion' => 'América Central',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Guatemala',
                'codigo_iso2' => 'GT',
                'codigo_numerico' => '320',
                'prefijo_telefono' => '+502',
                'id_moneda' => 2, // Quetzal
                'region' => 'América',
                'subregion' => 'América Central',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'El Salvador',
                'codigo_iso2' => 'SV',
                'codigo_numerico' => '222',
                'prefijo_telefono' => '+503',
                'id_moneda' => 3, // Dólar
                'region' => 'América',
                'subregion' => 'América Central',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Nicaragua',
                'codigo_iso2' => 'NI',
                'codigo_numerico' => '558',
                'prefijo_telefono' => '+505',
                'id_moneda' => 4, // Córdoba
                'region' => 'América',
                'subregion' => 'América Central',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Costa Rica',
                'codigo_iso2' => 'CR',
                'codigo_numerico' => '188',
                'prefijo_telefono' => '+506',
                'id_moneda' => 5, // Colón
                'region' => 'América',
                'subregion' => 'América Central',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Panamá',
                'codigo_iso2' => 'PA',
                'codigo_numerico' => '591',
                'prefijo_telefono' => '+507',
                'id_moneda' => 6, // Balboa
                'region' => 'América',
                'subregion' => 'América Central',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
