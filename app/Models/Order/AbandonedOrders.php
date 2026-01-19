<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Customer\Customers;
use App\Models\Market\Markets;
use App\Models\Product\Products;

class AbandonedOrders extends Model
{
    use SoftDeletes;

    protected $table = 'abandoned_orders';

    protected $fillable = [
        'user_id',
        'id_customer',
        'product_id',
        'quantity',
        'total_price',
        'subtotal_price',
        'id_market',
        'id_discount',
        'id_envio',
        'id_impuesto',
        'id_moneda',
        'note',
        'id_etiqueta',
        'id_status_prepared_order',
        'id_status_order',
        'cart_token',
        'email_sent_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'subtotal_price' => 'decimal:2',
        'email_sent_at' => 'datetime',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'id_customer');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function market()
    {
        return $this->belongsTo(Markets::class, 'id_market');
    }

    /**
     * Generar token único para el carrito
     */
    public function generateCartToken()
    {
        if (!$this->cart_token) {
            $this->cart_token = bin2hex(random_bytes(32));
            $this->save();
        }
        return $this->cart_token;
    }

    /**
     * Obtener URL del carrito
     */
    public function getCartUrl()
    {
        return route('cart.recover', ['token' => $this->cart_token]);
    }
}
