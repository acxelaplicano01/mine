<?php

namespace App\Models\Product\Transfer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product\Products;
use App\Models\Product\Transfer\StatusTransfer;
use App\Models\PointSale\Branch\Branches;
use Database\Factories\TransfersFactory;

class Transfers extends Model
{
    use HasFactory;
    
    protected $table = 'transfers';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return TransfersFactory::new();
    }

    protected $fillable = [
        'id_sucursal_origen',
        'id_sucursal_destino',
        'fecha_envio_creacion',
        'id_product',
        'cantidad',
        'nombre_referencia',
        'nota_interna',
        'id_etquetas',
        'id_status_transfer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'nota_interna' => 'array',
        'fecha_envio_creacion' => 'datetime',
    ];

    /**
     * Relación con Producto
     */
    public function product()
    {
        return $this->belongsTo(Products::class, 'id_product');
    }

    /**
     * Relación con Estado de Transferencia
     */
    public function status()
    {
        return $this->belongsTo(StatusTransfer::class, 'id_status_transfer');
    }

    /**
     * Relación con Branch Origen
     */
    public function sucursalOrigen()
    {
        return $this->belongsTo(Branches::class, 'id_sucursal_origen');
    }

    /**
     * Relación con Branch Destino
     */
    public function sucursalDestino()
    {
        return $this->belongsTo(Branches::class, 'id_sucursal_destino');
    }
}
