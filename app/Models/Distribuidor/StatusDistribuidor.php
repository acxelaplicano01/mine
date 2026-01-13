<?php

namespace App\Models\Distribuidor;

use Illuminate\Database\Eloquent\Model;

class StatusDistribuidor extends Model
{
    protected $table = 'status_distribuidores';

    protected $fillable = [
        'name',
        'description',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];
}
