<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'transaction_id',
        'gateway_reference',
        'user_id',
        'track_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'gateway_response',
        'subscription_days',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'successful';
    }
}
