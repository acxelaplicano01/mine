<?php

namespace App\Models\Product\Transfer;

use Illuminate\Database\Eloquent\Model;

class StatusTransfer extends Model
{
    protected $table = 'status_transfers';

    protected $fillable = [
        'nombre_status',
        'descripcion_status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}