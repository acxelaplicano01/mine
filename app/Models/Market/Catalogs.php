<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Model;

class Catalogs extends Model
{
    protected $table = 'catalogs';

    protected $fillable = [
        'name',
        'description',
        'price',
        'id_moneda',
        'ajuste_price',
        'reducir_aumentar',
        'price_comparacion',
        'id_product',
        'id_market',
        'id_status_catalog',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'reducir_aumentar' => 'boolean',
    ];
}
