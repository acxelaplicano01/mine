<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class VariantProduct extends Model
{
    protected $table = 'variant_products';

    protected $fillable = [
        'product_id', 
        'sku',
        'barcode',
        'price', 
        'cantidad_inventario', 
        'weight', 
        'name_variant',
        'valores_variante',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'valores_variante' => 'array',
    ];
}
