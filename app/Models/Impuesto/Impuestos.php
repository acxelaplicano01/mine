<?php

namespace App\Models\Impuesto;

use Illuminate\Database\Eloquent\Model;

class Impuestos extends Model
{
    protected $table = 'impuestos';

    protected $fillable = [
        'nombre_impuesto',
        'porcentaje_impuesto',
        'descripcion_impuesto',
    ];
}
