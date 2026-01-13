<?php

namespace App\Models\Etiquetas;

use Illuminate\Database\Eloquent\Model;

class Etiquetas extends Model
{
    protected $table = 'etiquetas';

    protected $fillable = [
        'name',
        'color',
        'description',
    ];

    protected $casts = [
        'color' => 'array',
    ];
}
