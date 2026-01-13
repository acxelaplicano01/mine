<?php

namespace App\Models\Product\Inventory;

use Illuminate\Database\Eloquent\Model;

class StatusInventory extends Model
{
    protected $table = 'status_inventories';

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
