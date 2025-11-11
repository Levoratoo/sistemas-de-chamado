<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_WAITING_USER = 'waiting_user';
    public const STATUS_FINALIZED = 'finalized';

    protected $fillable = [
        'code',
        'title',
        'description',
        'area_id',
        'category_id',
        'priority',
        'status',
        'requester_id',
        'opened_on_behalf_of',
        'assignee_id',
        'assigned_at',
        'started_at',
        'first_response_at',
        'due_at',
        'respond_by',
        'resolved_at',
        'last_status_change_at',
        'resolution_summary',
        'resolution_by',
        'closed_at',
        'request_type',
        'company',
        'cost_center',
        'approver_id',
        'payment_amount',
        'payment_date',
        'payment_type',
        'bank_data',
        'employee_code',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'respond_by' => 'datetime',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'last_status_change_at' => 'datetime',
        'closed_at' => 'datetime',
        'payment_date' => 'date',
        'payment_amount' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function openedOnBehalfOf(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_on_behalf_of');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolution_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TicketEvent::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(TicketEvaluation::class);
    }

    /**
     * Verifica se o ticket pode ser avaliado pelo usuário
     */
    public function canBeEvaluatedBy(User $user): bool
    {
        // Só pode avaliar se o ticket está finalizado
        if ($this->status !== self::STATUS_FINALIZED) {
            return false;
        }

        // Só quem solicitou o ticket pode avaliar
        if ($this->requester_id !== $user->id) {
            return false;
        }

        // Não pode avaliar duas vezes
        return !TicketEvaluation::hasUserEvaluated($this->id, $user->id);
    }

    /**
     * Obtém a avaliação média do ticket
     */
    public function getAverageRatingAttribute(): float
    {
        return TicketEvaluation::getTicketAverageRating($this->id);
    }

    public function scopeFinalizedBy(Builder $query, int $userId): Builder
    {
        return $query->where('resolution_by', $userId)
            ->where('status', self::STATUS_FINALIZED);
    }

    public function scopeResolvedInMonth(Builder $query, int $year, int $month): Builder
    {
        $start = now()->setDate($year, $month, 1)->startOfDay();
        $end = (clone $start)->endOfMonth()->endOfDay();

        return $query->whereBetween('resolved_at', [$start, $end]);
    }

    public function getSlaStatusAttribute(): string
    {
        // Tickets finalizados sempre estão dentro do SLA
        if ($this->status === self::STATUS_FINALIZED) {
            return 'good';
        }

        $now = now();

        // Se não tem due_at, verificar se passaram 7 dias desde a criação
        if (!$this->due_at instanceof CarbonInterface) {
            if ($this->created_at instanceof CarbonInterface) {
                $sevenDaysFromCreation = $this->created_at->copy()->addDays(7);
                if ($now->isAfter($sevenDaysFromCreation)) {
                    return 'overdue';
                }
            }
            return 'good';
        }

        // Se due_at está no futuro, calcular porcentagem restante
        if ($this->due_at->isFuture()) {
            if (!$this->created_at instanceof CarbonInterface) {
                return 'good';
            }

            $timeRemaining = max($now->diffInMinutes($this->due_at, false) * -1, 0);
            $totalTime = max($this->created_at->diffInMinutes($this->due_at), 1);
            $percentageRemaining = ($timeRemaining / $totalTime) * 100;

            if ($percentageRemaining <= 25) {
                return 'warning';
            }

            return 'good';
        }

        // Se due_at está no passado, verificar se realmente está atrasado
        if ($this->due_at->isPast()) {
            // Se due_at é anterior à criação, definitivamente é um erro - ignorar e usar fallback
            if ($this->created_at instanceof CarbonInterface) {
                if ($this->due_at->isBefore($this->created_at)) {
                    // Usar fallback de 7 dias a partir da criação
                    $sevenDaysFromCreation = $this->created_at->copy()->addDays(7);
                    return $now->isAfter($sevenDaysFromCreation) ? 'overdue' : 'good';
                }
                
                // Se ticket foi criado há menos de 1 dia, usar fallback para evitar falsos positivos
                $oneDayAfterCreation = $this->created_at->copy()->addDay();
                if ($now->isBefore($oneDayAfterCreation)) {
                    $sevenDaysFromCreation = $this->created_at->copy()->addDays(7);
                    return $now->isAfter($sevenDaysFromCreation) ? 'overdue' : 'good';
                }
            }
            
            // Se due_at é posterior à criação mas já passou e ticket tem mais de 1 dia, está realmente atrasado
            return 'overdue';
        }

        // Se due_at é hoje, verificar se já passou
        if ($now->isAfter($this->due_at)) {
            return 'overdue';
        }

        return 'good';
    }

    public function getSlaBadgeClassAttribute(): string
    {
        return match ($this->sla_status) {
            'overdue' => 'badge-danger',
            'warning' => 'badge-warning',
            'good' => 'badge-success',
            default => 'badge-info',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'badge-info',
            self::STATUS_IN_PROGRESS => 'badge-warning',
            self::STATUS_WAITING_USER => 'badge-warning',
            self::STATUS_FINALIZED => 'badge-success',
            default => 'badge-info',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'badge-info',
            'medium' => 'badge-warning',
            'high' => 'badge-danger',
            'critical' => 'badge-danger',
            default => 'badge-info',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'Aberto',
            self::STATUS_IN_PROGRESS => 'Em andamento',
            self::STATUS_WAITING_USER => 'Aguardando usuario',
            self::STATUS_FINALIZED => 'Finalizado',
            default => 'Desconhecido',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Baixa',
            'medium' => 'Media',
            'high' => 'Alta',
            'critical' => 'Critica',
            default => 'Media',
        };
    }

    public function isOverdue(): bool
    {
        // Ticket finalizado nunca está vencido
        if ($this->status === self::STATUS_FINALIZED) {
            return false;
        }
        
        // Verificar se passaram mais de 7 dias desde a criação
        if ($this->created_at instanceof CarbonInterface) {
            $sevenDaysFromCreation = $this->created_at->copy()->addDays(7);
            if (now()->isAfter($sevenDaysFromCreation)) {
                return true;
            }
        }
        
        // Verificar due_at apenas se for maior que created_at (SLA calculado corretamente)
        if ($this->due_at instanceof CarbonInterface 
            && $this->created_at instanceof CarbonInterface
            && $this->due_at->greaterThan($this->created_at)
            && $this->due_at->lessThan(now())) {
            return true;
        }
        
        return false;
    }

    public function isNearDue(): bool
    {
        if (!$this->due_at instanceof CarbonInterface || !$this->created_at instanceof CarbonInterface) {
            return false;
        }

        $timeRemaining = max(now()->diffInMinutes($this->due_at, false) * -1, 0);
        $totalTime = max($this->created_at->diffInMinutes($this->due_at), 1);
        $percentageRemaining = ($timeRemaining / $totalTime) * 100;

        return $percentageRemaining <= 25 && !$this->isOverdue();
    }
}

