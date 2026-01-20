<?php

namespace App\Models\Product\Collection;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Products;

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
        'conditions',
        'condition_match',
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
        'conditions' => 'array',
        'condition_match' => 'string',
    ];

    /**
     * Relación muchos a muchos con productos
     */
    public function products()
    {
        return $this->belongsToMany(Products::class, 'collection_products', 'collection_id', 'product_id')
            ->withPivot('variant_id', 'sort_order')
            ->withTimestamps()
            ->orderBy('collection_products.sort_order');
    }

    /**
     * Relación con la tabla intermedia
     */
    public function collectionProducts()
    {
        return $this->hasMany(CollectionProduct::class, 'collection_id');
    }
}
