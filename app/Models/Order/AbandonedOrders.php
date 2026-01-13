<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class AbandonedOrders extends Model
{
    protected $table = 'abandoned_orders';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'total_price',
        'id_market',
        'id_discount',
        'id_envio',
        'id_impuesto',
        'id_moneda',
        'note',
        'id_etiqueta',
        'id_status_prepared_order',
        'id_status_order',
    ];
}
