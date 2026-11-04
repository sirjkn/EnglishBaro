<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    public const WAITING_FOR_ANSWER = 'waiting_for_answer';

    public const ANSWERED = 'answered';

    public const CLOSED = 'closed';

    protected $fillable = [
        'user_id',
        'subject',
        'status',
        'last_reply_at',
    ];

    protected function casts(): array
    {
        return [
            'last_reply_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class)->orderBy('created_at');
    }

    public function isClosed(): bool
    {
        return $this->status === self::CLOSED;
    }
}
