<?php

namespace App\Models\Discount;

use Illuminate\Database\Eloquent\Model;

class MethodDiscount extends Model
{
    protected $table = 'method_discounts';

    protected $fillable = [
        'name_method_discount',
        'description',
    ];
}
