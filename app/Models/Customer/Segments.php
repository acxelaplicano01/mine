<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class Segments extends Model
{
    protected $table = 'customer_segments';

    protected $fillable = [
        'name',
        'description',
        'id_customer',
    ];
}
