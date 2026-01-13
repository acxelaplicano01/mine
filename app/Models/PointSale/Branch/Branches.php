<?php

namespace App\Models\PointSale\Branch;

use Illuminate\Database\Eloquent\Model;

class Branches extends Model
{
    protected $table = 'branches';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'id_inventory',
        'id_status_branch',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];
}
