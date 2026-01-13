<?php

namespace App\Models\Product\OrderPurchase;

use Illuminate\Database\Eloquent\Model;

class OrderPurchases extends Model
{
    protected $table = 'order_purchases';

    protected $fillable = [
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
        //agregar costos/gastos ya sea por fletes
        //ajuste y importe

    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'nota_al_distribuidor' => 'array',
    ];
}
