<?php

namespace App\Models\Distribuidor;

use Illuminate\Database\Eloquent\Model;

class Distribuidores extends Model
{
    protected $table = 'distribuidores';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'id_status_distribuidor',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];
}
