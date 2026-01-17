<?php

namespace App\Models\Pais;

use Illuminate\Database\Eloquent\Model;

class Paises extends Model
{
    protected $table = 'paises';

    protected $fillable = [
        'nombre',
        'codigo_iso2',
        'codigo_numerico',
        'prefijo_telefono',
        'id_moneda',
        'region',
        'subregion',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];
}
