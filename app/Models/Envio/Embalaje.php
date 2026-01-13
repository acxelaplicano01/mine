<?php

namespace App\Models\Envio;

use Illuminate\Database\Eloquent\Model;

class Embalaje extends Model
{
    protected $table = 'embalajes';

    protected $fillable = [
        'tipo_embalaje',
        'dimensiones',
        'material',
        'peso_maximo',
    ];
    
    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];

}
