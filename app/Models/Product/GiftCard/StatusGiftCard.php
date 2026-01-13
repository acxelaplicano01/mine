<?php

namespace App\Models\Product\GiftCard;

use Illuminate\Database\Eloquent\Model;

class StatusGiftCard extends Model
{
    protected $table = 'status_gift_cards';

    protected $fillable = [
        'nombre_status',
        'descripcion_status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
