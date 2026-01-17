<?php

namespace App\Models\Discount;

use Illuminate\Database\Eloquent\Model;

class TypeDiscount extends Model
{
    protected $table = 'type_discounts';

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
