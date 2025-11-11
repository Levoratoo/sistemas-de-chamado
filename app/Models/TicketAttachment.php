<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'filename',
        'path',
        'mime',
        'size',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIconAttribute(): string
    {
        $mime = $this->mime;
        
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        
        if (str_starts_with($mime, 'application/pdf')) {
            return 'pdf';
        }
        
        if (str_starts_with($mime, 'text/')) {
            return 'document';
        }
        
        if (str_contains($mime, 'word') || str_contains($mime, 'excel') || str_contains($mime, 'powerpoint')) {
            return 'document';
        }
        
        return 'file';
    }
}











