<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom',
        'prenom',
        'image',
        'statut',
        'administrator_id',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Constantes pour les statuts
     */
    public const STATUT_INACTIF = 'inactif';
    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_ACTIF = 'actif';

    /**
     * Get the administrator that created this profile.
     */
    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class);
    }

    /**
     * Get all comments for this profile.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Scope to get only active profiles.
     */
    public function scopeActive($query)
    {
        return $query->where('statut', self::STATUT_ACTIF);
    }
}
