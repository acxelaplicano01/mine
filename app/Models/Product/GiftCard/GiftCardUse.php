<?php

namespace App\Models\Product\GiftCard;

use Illuminate\Database\Eloquent\Model;

class GiftCardUse extends Model
{
    protected $table = 'gift_card_uses';

    protected $fillable = [
        'gift_card_id',
        'amount',
        'description',
        'used_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'used_at' => 'datetime',
    ];

    public function giftCard()
    {
        return $this->belongsTo(GiftCards::class, 'gift_card_id');
    }
}