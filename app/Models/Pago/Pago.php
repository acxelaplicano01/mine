<?php

namespace App\Models\Pago;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'monto',
        'metodo_pago',
        'estado_pago',
        'id_pedido',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];
}
