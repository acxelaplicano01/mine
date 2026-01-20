<?php

namespace App\Models\Product\OrderPurchase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Distribuidor\Distribuidores;
use App\Models\Product\Products;
use Database\Factories\OrderPurchasesFactory;

class OrderPurchases extends Model
{
    use HasFactory;
    
    protected $table = 'order_purchases';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return OrderPurchasesFactory::new();
    }

    protected $fillable = [
        'estado',
        'id_distribuidor',
        'id_sucursal_destino',
        'id_condiciones_pago',
        'id_moneda_del_distribuidor',
        'fecha_llegada_estimada',
        'id_empresa_trasnportista',
        'numero_guia',
        'id_product',
        'numero_referencia',
        'nota_al_distribuidor',
        'id_etquetas',
        'ajustes_costos',
        'subtotal',
        'impuestos',
        'envio',
        'total',
        'productos',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'nota_al_distribuidor' => 'array',
        'fecha_llegada_estimada' => 'date',
        'ajustes_costos' => 'array',
        'productos' => 'array',
    ];

    /**
     * Relación con Distribuidor
     */
    public function distribuidor()
    {
        return $this->belongsTo(Distribuidores::class, 'id_distribuidor');
    }

    /**
     * Relación con Producto
     */
    public function product()
    {
        return $this->belongsTo(Products::class, 'id_product');
    }
}
