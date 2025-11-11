<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isAdmin(): bool
    {
        return $this->name === 'admin';
    }

    public function isGestor(): bool
    {
        return $this->name === 'gestor';
    }

    public function isAtendente(): bool
    {
        return $this->name === 'atendente';
    }

    public function isUsuario(): bool
    {
        return $this->name === 'usuario';
    }
}

