<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusEmployeeSeeder extends Seeder
{
    public function run()
    {
        DB::table('status_employees')->insert([
            [
                'name' => 'Activo',
                'description' => 'Empleado activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inactivo',
                'description' => 'Empleado inactivo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'De vacaciones',
                'description' => 'Empleado en período de vacaciones',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Suspendido',
                'description' => 'Empleado suspendido',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
