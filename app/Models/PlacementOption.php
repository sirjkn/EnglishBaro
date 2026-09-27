<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlacementOption extends Model
{
    protected $fillable = ['placement_question_id', 'option_text', 'track_id', 'order'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(PlacementQuestion::class, 'placement_question_id');
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
