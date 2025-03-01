<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramUser extends Model
{
    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'last_name',
    ];

    public function initiatedPartnerships(): HasMany
    {
        return $this->hasMany(Partnership::class, 'user1_id');
    }

    public function receivedPartnerships(): HasMany
    {
        return $this->hasMany(Partnership::class, 'user2_id');
    }

    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_user_id');
    }

    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_user_id');
    }

    public function getAllPartnerships()
    {
        return $this->initiatedPartnerships->merge($this->receivedPartnerships);
    }
}