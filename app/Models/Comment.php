<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'contenu',
        'administrator_id',
        'profile_id',
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
     * Get the administrator who posted this comment.
     */
    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Administrator::class);
    }

    /**
     * Get the profile this comment belongs to.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
