<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sla extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'priority',
        'response_time_minutes',
        'resolve_time_minutes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getResponseTimeAttribute(): string
    {
        $hours = floor($this->response_time_minutes / 60);
        $minutes = $this->response_time_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'min';
        }
        
        return $minutes . 'min';
    }

    public function getResolveTimeAttribute(): string
    {
        $hours = floor($this->resolve_time_minutes / 60);
        $minutes = $this->resolve_time_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'min';
        }
        
        return $minutes . 'min';
    }
}











