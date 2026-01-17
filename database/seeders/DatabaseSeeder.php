<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario de prueba
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Ejecutar seeders en orden
        $this->call([
            // Monedas y países
            MonedaSeeder::class,
            PaisSeeder::class,
            IdiomaSeeder::class,
            
            // Markets
            MarketSeeder::class,
            
            // Clientes
            CustomerSeeder::class,
            SegmentSeeder::class,
            
            // Productos
            ProductSeeder::class,
            VariantProductSeeder::class,
            
            // Descuentos
            TypeDiscountSeeder::class,
            MethodDiscountSeeder::class,
            ElegibilityDiscountSeeder::class,
            RequirementDiscountSeeder::class,
            StatusDiscountSeeder::class,
            DiscountSeeder::class,
            
            // Estados de órdenes
            StatusOrderSeeder::class,
            StatusPreparedOrderSeeder::class,
            ConditionPaySeeder::class,
            
            // Impuestos
            ImpuestoSeeder::class,
            
            // Estados de entidades
            StatusDistribuidorSeeder::class,
            StatusBranchSeeder::class,
            StatusEmployeeSeeder::class,
            StatusTransportistaSeeder::class,
            
            // Órdenes
            OrderSeeder::class,
        ]);
    }
}
