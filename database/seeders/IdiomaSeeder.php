<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IdiomaSeeder extends Seeder
{
    public function run()
    {
        DB::table('idiomas')->insert([
            [
                'name' => 'Español',
                'code' => 'es',
                'locale' => 'es_ES',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inglés',
                'code' => 'en',
                'locale' => 'en_US',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Portugués',
                'code' => 'pt',
                'locale' => 'pt_BR',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
