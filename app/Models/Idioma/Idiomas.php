<?php

namespace App\Models\Idioma;

use Illuminate\Database\Eloquent\Model;

class Idiomas extends Model
{
    protected $table = 'idiomas';

    protected $fillable = [
        'name',
        'code',
        'locale',
        'is_active',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'deleted_at'
    ];
}
