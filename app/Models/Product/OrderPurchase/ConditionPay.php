<?php

namespace App\Models\Product\OrderPurchase;

use Illuminate\Database\Eloquent\Model;

class ConditionPay extends Model
{
    protected $table = 'condition_pay';

    protected $fillable = [
        'nombre_condicion',
        'descripcion_condicion',
        'dias_vencimiento',
        'type',
        'is_active',
    ];
}
