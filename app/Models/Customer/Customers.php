<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'name',
        'last_name',
        'language',
        'acepta_mensajes',
        'acepta_email',
        'email',
        'phone',
        'address',
        'total_spent', 
        'orders_count',
        'id_info_fiscal',
        'notas',
        'id_etiqueta',
    ];

    protected $casts = [
        'acepta_mensajes' => 'boolean',
        'acepta_email' => 'boolean',
    ];

}
