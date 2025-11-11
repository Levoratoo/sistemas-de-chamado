<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function requestTypes(): HasMany
    {
        return $this->hasMany(RequestType::class)->orderBy('sort_order');
    }

    public function activeRequestTypes(): HasMany
    {
        return $this->hasMany(RequestType::class)->where('active', true)->orderBy('sort_order');
    }
}