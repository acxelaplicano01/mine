<?php

namespace App\Models\Product\OrderPurchase;

use Illuminate\Database\Eloquent\Model;

class TypeGasto extends Model
{
    protected $table = 'type_gasto';

    protected $fillable = [
        'nombre_tipo_gasto',
        'descripcion_tipo_gasto',
    ];
}
