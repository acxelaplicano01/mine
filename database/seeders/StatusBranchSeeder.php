<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusBranchSeeder extends Seeder
{
    public function run()
    {
        DB::table('status_branches')->insert([
            [
                'name' => 'Activa',
                'description' => 'Sucursal activa y operativa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inactiva',
                'description' => 'Sucursal inactiva',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'En mantenimiento',
                'description' => 'Sucursal en mantenimiento',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
