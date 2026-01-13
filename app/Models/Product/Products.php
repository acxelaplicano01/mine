<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'multimedia',
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

    public function variants()
    {
        return $this->hasMany(VariantProduct::class, 'product_id');
    }

}
