<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'partnership_id',
        'from_user_id',
        'to_user_id',
        'amount',
        'description',
        'type'
    ];

    public const TYPE_DEBT = 'debt';
    public const TYPE_DEMAND = 'demand';

    public function partnership(): BelongsTo
    {
        return $this->belongsTo(Partnership::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'to_user_id');
    }

    public function isDebt(): bool
    {
        return $this->type === self::TYPE_DEBT;
    }

    public function isDemand(): bool
    {
        return $this->type === self::TYPE_DEMAND;
    }
}