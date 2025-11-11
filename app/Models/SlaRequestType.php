<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaRequestType extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_type',
        'priority',
        'response_time_minutes',
        'resolve_time_minutes',
        'active',
        'description',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Obter SLA por tipo de chamado e prioridade
     */
    public static function getSlaForRequestType(string $requestType, string $priority = 'medium'): ?self
    {
        return self::where('request_type', $requestType)
            ->where('priority', $priority)
            ->where('active', true)
            ->first();
    }

    /**
     * Obter SLA padrão quando não encontrar específico
     */
    public static function getDefaultSla(): self
    {
        return self::firstOrCreate(
            ['request_type' => 'default', 'priority' => 'medium'],
            [
                'response_time_minutes' => 240, // 4 horas
                'resolve_time_minutes' => 1440, // 24 horas
                'active' => true,
                'description' => 'SLA padrão para tickets sem configuração específica',
            ]
        );
    }

    /**
     * Formatar tempo de resposta
     */
    public function getFormattedResponseTimeAttribute(): string
    {
        return $this->formatTime($this->response_time_minutes);
    }

    /**
     * Formatar tempo de resolução
     */
    public function getFormattedResolveTimeAttribute(): string
    {
        return $this->formatTime($this->resolve_time_minutes);
    }

    /**
     * Formatar tempo em minutos para string legível
     */
    private function formatTime(int $minutes): string
    {
        $days = floor($minutes / 1440);
        $hours = floor(($minutes % 1440) / 60);
        $mins = $minutes % 60;

        $parts = [];
        if ($days > 0) $parts[] = $days . 'd';
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($mins > 0) $parts[] = $mins . 'min';

        return implode(' ', $parts) ?: '0min';
    }

    /**
     * Calcular data de vencimento baseada no SLA
     */
    public function calculateDueDate(\Carbon\Carbon $from = null): \Carbon\Carbon
    {
        $from = $from ?? now();
        return $from->copy()->addMinutes($this->resolve_time_minutes);
    }

    /**
     * Calcular data de primeira resposta baseada no SLA
     */
    public function calculateResponseDate(\Carbon\Carbon $from = null): \Carbon\Carbon
    {
        $from = $from ?? now();
        return $from->copy()->addMinutes($this->response_time_minutes);
    }
}