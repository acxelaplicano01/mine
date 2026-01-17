<?php

namespace App\Models\Discount;

use Illuminate\Database\Eloquent\Model;

class StatusDiscount extends Model
{
    protected $table = 'status_discounts';

    protected $fillable = [
        'name_status_discount',
        'description',
    ];
}
