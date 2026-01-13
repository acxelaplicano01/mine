<?php

namespace App\Models\Dominio;

use Illuminate\Database\Eloquent\Model;

class Dominios extends Model
{
    protected $table = 'dominios';

    protected $fillable = [
        'name',
        'description',
        'url',
        'id_status_dominio',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];
}
