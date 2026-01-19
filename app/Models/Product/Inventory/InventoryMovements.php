<?php

namespace App\Models\Product\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Product\Products;
use App\Models\Product\VariantProduct;
use App\Models\User;

class InventoryMovements extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'id_product',
        'id_variant',
        'id_inventory',
        'type',
        'quantity',
        'cantidad_anterior',
        'cantidad_nueva',
        'reason',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'cantidad_anterior' => 'integer',
        'cantidad_nueva' => 'integer',
    ];

    // Relaciones
    public function product()
    {
        return $this->belongsTo(Products::class, 'id_product');
    }

    public function variant()
    {
        return $this->belongsTo(VariantProduct::class, 'id_variant');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventories::class, 'id_inventory');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
