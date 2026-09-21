<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'phone',
        'country',
        'referral_email',
        'level_id',
        'profile_image_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function profileImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'profile_image_id');
    }
}
