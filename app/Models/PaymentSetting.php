<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = ['gateway', 'is_enabled', 'is_sandbox', 'config'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_sandbox' => 'boolean',
            'config' => 'encrypted:array',
        ];
    }
}
