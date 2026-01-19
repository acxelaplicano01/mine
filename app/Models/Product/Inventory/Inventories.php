<?php

namespace App\Models\Product\Inventory;

use Illuminate\Database\Eloquent\Model;

class Inventories extends Model
{
    protected $table = 'inventories';

    protected $fillable = [
        'id_product',
        'cantidad_inventario',
        'cantidad_no_disponible',
        'seguimiento_inventario',
        'location',
        'id_status_inventory',
        'umbral_aviso_inventario',
        'permitir_vender_sin_inventario',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'cantidad_inventario' => 'integer',
        'location' => 'string',
        'id_status_inventory' => 'string',
        'seguimiento_inventario' => 'boolean',
        'umbral_aviso_inventario' => 'integer',
        'permitir_vender_sin_inventario' => 'boolean',
        'sku' => 'string',
        'barcode' => 'string',
    ];

    // Relaciones
    public function product()
    {
        return $this->belongsTo(\App\Models\Product\Products::class, 'id_product');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovements::class, 'id_inventory');
    }
}
