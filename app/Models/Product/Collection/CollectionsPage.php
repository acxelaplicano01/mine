<?php

namespace App\Models\Product\Collection;

use Illuminate\Database\Eloquent\Model;

class CollectionsPage extends Model
{
    protected $table = 'collections';

    protected $fillable = [
        'name',
        'description',
        'id_tipo_collection',
        'id_product',
        'id_publicacion',
        //'id_plantilla',
        'image_url',
        'id_status_collection',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'image_url' => 'string',
        'id_status_collection' => 'string',
    ];
}
