<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestType extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_area_id',
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

    public function requestArea(): BelongsTo
    {
        return $this->belongsTo(RequestArea::class);
    }
}