<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Product\Products;
use App\Models\Product\VariantProduct;

class DraftItems extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'draft_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'subtotal' => 'decimal:4',
    ];

    public function draft()
    {
        return $this->belongsTo(Drafts::class, 'draft_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(VariantProduct::class, 'variant_id');
    }
}
