<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Model;

class StatusMarket extends Model
{
    protected $table = 'status_markets';

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
