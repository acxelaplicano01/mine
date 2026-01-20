<?php

namespace App\Models\PointSale\Branch;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Transfer\Transfers;

class Branches extends Model
{
    protected $table = 'branches';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'id_inventory',
        'id_status_branch',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Transferencias que tienen esta sucursal como origen
     */
    public function transferenciasOrigen()
    {
        return $this->hasMany(Transfers::class, 'id_sucursal_origen');
    }

    /**
     * Transferencias que tienen esta sucursal como destino
     */
    public function transferenciasDestino()
    {
        return $this->hasMany(Transfers::class, 'id_sucursal_destino');
    }
}
