<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer\Customers;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Juan',
                'last_name' => 'Pérez García',
                'language' => 'es',
                'acepta_mensajes' => true,
                'acepta_email' => true,
                'email' => 'juan.perez@example.com',
                'phone' => '+34 612 345 678',
                'address' => 'Calle Mayor 123, Madrid, España',
                'total_spent' => 1250.50,
                'orders_count' => 5,
                'notas' => 'Cliente frecuente, prefiere envíos express',
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subMonths(6),
            ],
            [
                'name' => 'María',
                'last_name' => 'González López',
                'language' => 'es',
                'acepta_mensajes' => true,
                'acepta_email' => false,
                'email' => 'maria.gonzalez@example.com',
                'phone' => '+34 623 456 789',
                'address' => 'Avenida Libertad 45, Barcelona, España',
                'total_spent' => 850.00,
                'orders_count' => 3,
                'notas' => 'Prefiere productos ecológicos',
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subMonths(4),
            ],
            [
                'name' => 'Carlos',
                'last_name' => 'Martínez Ruiz',
                'language' => 'es',
                'acepta_mensajes' => false,
                'acepta_email' => true,
                'email' => 'carlos.martinez@example.com',
                'phone' => '+34 634 567 890',
                'address' => 'Plaza España 78, Valencia, España',
                'total_spent' => 2100.75,
                'orders_count' => 8,
                'notas' => 'Cliente VIP, descuento del 10%',
                'created_at' => now()->subMonths(8),
                'updated_at' => now()->subMonths(8),
            ],
            [
                'name' => 'Ana',
                'last_name' => 'Rodríguez Fernández',
                'language' => 'es',
                'acepta_mensajes' => true,
                'acepta_email' => true,
                'email' => 'ana.rodriguez@example.com',
                'phone' => '+34 645 678 901',
                'address' => 'Calle del Sol 12, Sevilla, España',
                'total_spent' => 450.25,
                'orders_count' => 2,
                'notas' => 'Nueva cliente',
                'created_at' => now()->subMonth(),
                'updated_at' => now()->subMonth(),
            ],
            [
                'name' => 'Luis',
                'last_name' => 'Sánchez Torres',
                'language' => 'es',
                'acepta_mensajes' => true,
                'acepta_email' => true,
                'email' => 'luis.sanchez@example.com',
                'phone' => '+34 656 789 012',
                'address' => 'Paseo Marítimo 89, Málaga, España',
                'total_spent' => 1680.90,
                'orders_count' => 6,
                'notas' => 'Prefiere pago contra reembolso',
                'created_at' => now()->subMonths(5),
                'updated_at' => now()->subMonths(5),
            ],
            [
                'name' => 'Laura',
                'last_name' => 'Díaz Moreno',
                'language' => 'es',
                'acepta_mensajes' => false,
                'acepta_email' => false,
                'email' => 'laura.diaz@example.com',
                'phone' => '+34 667 890 123',
                'address' => 'Calle Nueva 34, Zaragoza, España',
                'total_spent' => 320.00,
                'orders_count' => 1,
                'notas' => null,
                'created_at' => now()->subWeeks(2),
                'updated_at' => now()->subWeeks(2),
            ],
            [
                'name' => 'Pedro',
                'last_name' => 'López Jiménez',
                'language' => 'es',
                'acepta_mensajes' => true,
                'acepta_email' => true,
                'email' => 'pedro.lopez@example.com',
                'phone' => '+34 678 901 234',
                'address' => 'Avenida Principal 156, Bilbao, España',
                'total_spent' => 3200.40,
                'orders_count' => 12,
                'notas' => 'Cliente corporativo, facturación mensual',
                'created_at' => now()->subYear(),
                'updated_at' => now()->subYear(),
            ],
            [
                'name' => 'Carmen',
                'last_name' => 'Hernández Castro',
                'language' => 'es',
                'acepta_mensajes' => true,
                'acepta_email' => true,
                'email' => 'carmen.hernandez@example.com',
                'phone' => '+34 689 012 345',
                'address' => 'Calle Flores 67, Granada, España',
                'total_spent' => 890.60,
                'orders_count' => 4,
                'notas' => 'Interesada en ofertas y promociones',
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ],
        ];

        foreach ($customers as $customer) {
            Customers::create($customer);
        }
    }
}
