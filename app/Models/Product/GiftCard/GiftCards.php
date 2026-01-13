<?php

namespace App\Models\Product\GiftCard;

use Illuminate\Database\Eloquent\Model;

class GiftCards extends Model
{
    protected $table = 'gift_cards';

    protected $fillable = [
        'code',
        'valor_inicial',
        'expiry_date',
        'id_customer',
        'id_status_gift_card',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'expiry_date' => 'datetime',
        'valor_inicial' => 'float',
        'code' => 'string',
    ];
}
