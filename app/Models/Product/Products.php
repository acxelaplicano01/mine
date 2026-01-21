<?php

namespace App\Models\Product;

use App\Models\Product\Inventory\Inventories;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'multimedia',
        'sku',
        'barcode',
        'id_category',
        'price_comparacion',
        'price_unitario',
        'cobrar_impuestos',
        'costo',
        'beneficio',
        'margen_beneficio',
        'id_inventory',
        'id_envio',
        //publicación en canal de ventas,
        'id_type_product',
        'id_distributor',
        'id_collection',
        'id_etiquetas',
        'id_status_product',

    ];
    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'multimedia' => 'array',
        'id_variants' => 'array',
        'cobrar_impuestos' => 'boolean',
    ];

    public function typeProduct()
    {
        return $this->belongsTo(TypeProduct::class, 'id_type_product');
    }

    public function distribuidor()
    {
        return $this->belongsTo(\App\Models\Distribuidor\Distribuidores::class, 'id_distributor');
    }

    public function etiqueta()
    {
        return $this->belongsTo(\App\Models\Etiquetas\Etiquetas::class, 'id_etiquetas');
    }

    public function variants()
    {
        return $this->hasMany(VariantProduct::class, 'product_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventories::class, 'id_inventory');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(\App\Models\Product\Inventory\InventoryMovements::class, 'id_product');
    }

}
