<?php

namespace App\Models\Envio;

use Illuminate\Database\Eloquent\Model;

class Envios extends Model
{
    protected $table = 'envios';

    protected $fillable = [
        'costo_envio',
        'id_embalaje',
        'peso',
        'unidad_peso',
        'producto_fisico',
        'info_aduana_id',
    ];
}
