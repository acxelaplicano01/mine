<?php

namespace App\Models\Notificacion;

use Illuminate\Database\Eloquent\Model;

class Notificaciones extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'titulo',
        'mensaje',
        'tipo_notificacion',
        'leida',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];
}
