<?php

namespace App\Models\Product\Collection;

use Illuminate\Database\Eloquent\Model;

class StatusCollection extends Model
{
    protected $table = 'status_collections';

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