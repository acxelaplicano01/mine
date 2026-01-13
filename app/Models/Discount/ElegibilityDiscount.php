<?php

namespace App\Models\Discount;

use Illuminate\Database\Eloquent\Model;

class ElegibilityDiscount extends Model
{
    protected $table = 'elegibility_discounts';

    protected $fillable = [
        'name',
        'description',
    ];
}
