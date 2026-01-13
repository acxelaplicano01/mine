<?php

namespace App\Models\Product\OrderPurchase;

use Illuminate\Database\Eloquent\Model;

class GastosPurchases extends Model
{
    protected $table = 'gastos_purchases';

    protected $fillable = [
        'id_order_purchase',
        'id_tipo_gasto',
        'descripcion_gasto',
        'monto_gasto',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}