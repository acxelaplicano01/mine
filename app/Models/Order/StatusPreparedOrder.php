<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class StatusPreparedOrder extends Model
{
    protected $table = 'status_prepared_orders';

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
