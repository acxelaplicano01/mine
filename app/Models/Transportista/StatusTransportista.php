<?php

namespace App\Models\Transportista;

use Illuminate\Database\Eloquent\Model;

class StatusTransportista extends Model
{
    protected $table = 'status_transportista';

    protected $fillable = [
        'nombre_status_transportista',
        'descripcion_status_transportista',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}