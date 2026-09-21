<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $fillable = ['email', 'ip_address', 'successful', 'user_agent'];

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
        ];
    }
}
