<?php

namespace App\Models\Product\Collection;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Products;
use App\Models\Product\VariantProduct;

class CollectionProduct extends Model
{
    protected $table = 'collection_products';

    protected $fillable = [
        'collection_id',
        'product_id',
        'variant_id',
        'sort_order',
    ];

    /**
     * Relación con producto
     */
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    /**
     * Relación con variante
     */
    public function variant()
    {
        return $this->belongsTo(VariantProduct::class, 'variant_id');
    }

    /**
     * Relación con colección
     */
    public function collection()
    {
        return $this->belongsTo(CollectionsPage::class, 'collection_id');
    }
}
