<?php

namespace App\Models\PointSale\Branch;

use Illuminate\Database\Eloquent\Model;

class StatusBranch extends Model
{
    protected $table = 'status_branches';

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
