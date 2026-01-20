<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PointSale\Branch\Branches;

class BranchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear sucursales predefinidas
        $branches = [
            [
                'name' => 'Sucursal Matriz',
                'address' => 'Av. Principal #123, Col. Centro',
                'phone' => '55-1234-5678',
                'email' => 'matriz@empresa.com',
                'id_inventory' => null,
                'id_status_branch' => 1,
            ],
            [
                'name' => 'Sucursal Norte',
                'address' => 'Blvd. Norte #456, Monterrey',
                'phone' => '81-9876-5432',
                'email' => 'norte@empresa.com',
                'id_inventory' => null,
                'id_status_branch' => 1,
            ],
            [
                'name' => 'Sucursal Occidente',
                'address' => 'Av. Chapultepec #789, Guadalajara',
                'phone' => '33-5555-1234',
                'email' => 'occidente@empresa.com',
                'id_inventory' => null,
                'id_status_branch' => 1,
            ],
            [
                'name' => 'Sucursal Sur',
                'address' => 'Calle Reforma #321, Mérida',
                'phone' => '999-444-7788',
                'email' => 'sur@empresa.com',
                'id_inventory' => null,
                'id_status_branch' => 1,
            ],
            [
                'name' => 'Sucursal Centro',
                'address' => 'Calle Madero #654, Puebla',
                'phone' => '222-333-9999',
                'email' => 'centro@empresa.com',
                'id_inventory' => null,
                'id_status_branch' => 1,
            ],
            [
                'name' => 'Sucursal Bajío',
                'address' => 'Av. Juárez #890, León',
                'phone' => '477-222-3333',
                'email' => 'bajio@empresa.com',
                'id_inventory' => null,
                'id_status_branch' => 1,
            ],
            [
                'name' => 'Sucursal Pacífico',
                'address' => 'Malecón #111, Mazatlán',
                'phone' => '669-111-2222',
                'email' => 'pacifico@empresa.com',
                'id_inventory' => null,
                'id_status_branch' => 1,
            ],
            [
                'name' => 'Sucursal Frontera',
                'address' => 'Av. Revolución #222, Tijuana',
                'phone' => '664-777-8888',
                'email' => 'frontera@empresa.com',
                'id_inventory' => null,
                'id_status_branch' => 1,
            ],
        ];

        foreach ($branches as $branch) {
            Branches::create($branch);
        }

        $this->command->info('✅ Se crearon ' . Branches::count() . ' sucursales (branches)');
    }
}
