<?php

namespace App\Models\Transportista;

use Illuminate\Database\Eloquent\Model;

class Transportistas extends Model
{
    protected $table = 'transportistas';

    protected $fillable = [
        'nombre_transportista',
        'telefono_transportista',
        'email_transportista',
        'direccion_transportista',
        'id_status_transportista',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'nombre_transportista' => 'string',
        'telefono_transportista' => 'string',
        'email_transportista' => 'string',
        'direccion_transportista' => 'string',
        'id_status_transportista' => 'string',
    ];
}
