<?php

namespace App\Models\Product\Transfer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusTransfer extends Model
{
    use SoftDeletes;
    
    protected $table = 'status_transfers';

    protected $fillable = [
        'name',
        'nombre_status',
        'descripcion_status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}