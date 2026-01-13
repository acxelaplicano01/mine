<?php

namespace App\Models\Product\Transfer;

use Illuminate\Database\Eloquent\Model;

class Transfers extends Model
{
    protected $table = 'transfers';

    protected $fillable = [
        'id_sucursal_origen',
        'id_sucursal_destino',
        'fecha_envio_creacion',
        //'fecha_entrega_estimada',
        //'id_transportista',
       // 'numero_guia',
        'id_product',
        'cantidad',
        'nombre_referencia',
        'nota_interna',
        'id_etquetas',
        'id_status_transfer',

    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'nota_interna' => 'array',
        'fecha_envio_creacion' => 'datetime',
    ];
}
