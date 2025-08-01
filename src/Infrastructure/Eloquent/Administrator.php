<?php

namespace Infrastructure\Eloquent;

use Database\Factories\AdministratorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Administrator extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): AdministratorFactory
    {
        return AdministratorFactory::new();
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
