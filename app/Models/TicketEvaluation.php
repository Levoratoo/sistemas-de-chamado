<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'rating',
        'comment',
        'evaluated_at',
    ];

    protected $casts = [
        'evaluated_at' => 'datetime',
        'rating' => 'integer',
    ];

    /**
     * Relacionamento com o ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relacionamento com o usuário que avaliou
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para avaliações por rating
     */
    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope para avaliações recentes
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('evaluated_at', '>=', now()->subDays($days));
    }

    /**
     * Verifica se um usuário já avaliou um ticket
     */
    public static function hasUserEvaluated(int $ticketId, int $userId): bool
    {
        return self::where('ticket_id', $ticketId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Calcula a média de avaliações de um ticket
     */
    public static function getTicketAverageRating(int $ticketId): float
    {
        return self::where('ticket_id', $ticketId)
            ->avg('rating') ?? 0;
    }

    /**
     * Calcula a média geral de satisfação
     */
    public static function getOverallSatisfactionRate(): float
    {
        $totalEvaluations = self::count();
        if ($totalEvaluations === 0) {
            return 0;
        }

        $satisfiedEvaluations = self::where('rating', '>=', 4)->count();
        return round(($satisfiedEvaluations / $totalEvaluations) * 100, 1);
    }
}