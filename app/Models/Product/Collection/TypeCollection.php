<?php

namespace App\Models\Product\Collection;

use Illuminate\Database\Eloquent\Model;

class TypeCollection extends Model
{
    protected $table = 'type_collections';

    protected $fillable = [
        'nombre_tipo',
        'descripcion_tipo',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
