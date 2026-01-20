<?php

namespace Database\Factories;

use App\Models\Order\Orders;
use App\Models\User\Users;
use App\Models\Shipping\ShippingMethods;
use App\Models\Payment\PaymentMethods;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Orders::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $tax = $subtotal * 0.16; // 16% IVA
        $total = $subtotal + $tax;
        
        $orderDate = $this->faker->dateTimeBetween('-2 years', 'now');
        $deliveryDate = $this->faker->dateTimeBetween($orderDate, '+30 days');

        return [
            'user_id' => $this->faker->numberBetween(1, 8),
            'order_date' => $orderDate,
            'delivery_date' => $deliveryDate,
            'shipping_address_id' => null,
            'billing_address_id' => null,
            'shipping_method_id' => $this->faker->numberBetween(1, 6),
            'payment_method_id' => $this->faker->numberBetween(1, 6),
            'shipping_cost' => $this->faker->randomFloat(2, 0, 100),
            'discount' => 0,
            'tax' => $tax,
            'subtotal' => $subtotal,
            'total' => $total,
            'status_id' => $this->faker->numberBetween(1, 5),
            'payment_status_id' => $this->faker->numberBetween(1, 5),
            'notes' => $this->faker->boolean(30) ? 'Nota generada automáticamente' : null,
            'tracking_number' => null,
            'warehouse_id' => $this->faker->numberBetween(1, 3),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}