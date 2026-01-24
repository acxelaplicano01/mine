<?php

namespace App\Models\Product\GiftCard;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer\Customers;

class GiftCards extends Model
{
    protected $table = 'gift_cards';

    protected $fillable = [
        'code',
        'valor_inicial',
        'valor_usado',
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
        'valor_usado' => 'float',
        'code' => 'string',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'id_customer');
    }

    public function uses()
    {
        return $this->hasMany(GiftCardUse::class, 'gift_card_id');
    }

    public function getValorRestanteAttribute()
    {
        return max(0, $this->valor_inicial - ($this->valor_usado ?? 0));
    }

    public function getPorcentajeUsadoAttribute()
    {
        if ($this->valor_inicial <= 0) return 100;
        return min(100, (($this->valor_usado ?? 0) / $this->valor_inicial) * 100);
    }

    public function canUse($amount)
    {
        if ($this->id_status_gift_card !== 1) {
            return false; // No está activa
        }
        
        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return false; // Expirada
        }
        
        return $this->valor_restante >= $amount;
    }

    public function use($amount, $description = null)
    {
        if (!$this->canUse($amount)) {
            throw new \Exception('No se puede usar esta tarjeta de regalo');
        }

        $this->valor_usado = ($this->valor_usado ?? 0) + $amount;
        
        // Si se agotó el saldo, marcar como usada
        if ($this->valor_restante <= 0) {
            $this->id_status_gift_card = 3; // Usado
        }
        
        $this->save();

        // Registrar el uso
        $this->uses()->create([
            'amount' => $amount,
            'description' => $description,
            'used_at' => now(),
        ]);

        return $this;
    }

    public function getStatusTextAttribute()
    {
        return match($this->id_status_gift_card) {
            1 => 'Activo',
            2 => 'Expirado',
            3 => 'Usado',
            default => 'Desconocido'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->id_status_gift_card) {
            1 => 'lime',
            2 => 'red',
            3 => 'zinc',
            default => 'zinc'
        };
    }
}
