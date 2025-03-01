<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Partnership extends Model
{
    protected $fillable = [
        'user1_id',
        'user2_id',
        'invite_token',
        'accepted_at'
    ];

    protected $casts = [
        'accepted_at' => 'datetime'
    ];

    public function user1(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'user1_id');
    }

    public function user2(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'user2_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function generateInviteToken(): string
    {
        $this->invite_token = Str::random(32);
        $this->save();
        return $this->invite_token;
    }

    public function accept(): void
    {
        $this->accepted_at = now();
        $this->invite_token = null;
        $this->save();
    }

    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }
}