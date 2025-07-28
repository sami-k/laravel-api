<?php

namespace Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('statut', self::STATUT_ACTIF);
    }
}
