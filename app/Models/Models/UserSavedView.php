<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSavedView extends Model
{
    protected $fillable = [
        'user_id',
        'view_type',
        'name',
        'filters',
        'search',
        'is_default',
        'sort_order'
    ];

    protected $casts = [
        'filters' => 'array',
        'is_default' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, $viewType)
    {
        return $query->where('view_type', $viewType);
    }
}
