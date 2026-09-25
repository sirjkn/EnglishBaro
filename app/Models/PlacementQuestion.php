<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlacementQuestion extends Model
{
    protected $fillable = ['question', 'order'];

    public function options(): HasMany
    {
        return $this->hasMany(PlacementOption::class)->orderBy('order');
    }
}
