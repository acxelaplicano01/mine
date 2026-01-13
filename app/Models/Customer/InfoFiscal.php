<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class InfoFiscal extends Model
{
    protected $table = 'customer_info_fiscal';

    protected $fillable = [
        'rfc',
        'razon_social',
        'direccion_fiscal',
    ];

    protected $casts = [
        'direccion_fiscal' => 'array',
    ];
}
