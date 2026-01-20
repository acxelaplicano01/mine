<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class TypeProduct extends Model
{
    protected $table = "type_products";
    protected $fillable = [
        'name',
        'description',
    ];
}
