<?php

namespace Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Builder;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'image',
        'statut',
        'administrator_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public const STATUT_INACTIF = 'inactif';
    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_ACTIF = 'actif';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ProfileFactory
    {
        return ProfileFactory::new();
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('statut', self::STATUT_ACTIF);
    }
}
