<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class StatusProduct extends Model
{
    protected $table = 'status_products';

    protected $fillable = [
        'nombre_status',
        'descripcion_status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
