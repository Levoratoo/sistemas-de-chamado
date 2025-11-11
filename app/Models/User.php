<?php

namespace App\Models;

use App\Models\Area;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'login',
        'password',
        'role_id',
        'team_id',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function requestedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class)->withTimestamps();
    }

    protected ?array $areaCache = null;

    public function groupsAreasIds(): array
    {
        if ($this->areaCache !== null) {
            return $this->areaCache;
        }

        $areas = $this->relationLoaded('areas')
            ? $this->areas
            : $this->areas()->select('areas.id')->get();

        $ids = $areas
            ->pluck('id')
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return $this->areaCache = $ids;
    }

    public function hasRole(array $roles): bool
    {
        $roleName = $this->role?->name;

        return $roleName !== null && in_array($roleName, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role?->isAdmin() ?? false;
    }

    public function isGestor(): bool
    {
        return $this->role?->isGestor() ?? false;
    }

    public function isAtendente(): bool
    {
        return $this->role?->isAtendente() ?? false;
    }

    public function isUsuario(): bool
    {
        return $this->role?->isUsuario() ?? false;
    }

    public function canManageTickets(): bool
    {
        return $this->hasRole(['admin', 'gestor', 'atendente']);
    }

    public function canManageUsers(): bool
    {
        return $this->hasRole(['admin', 'gestor']);
    }
}







