<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlacementAnswer extends Model
{
    protected $fillable = ['placement_attempt_id', 'placement_question_id', 'placement_option_id'];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(PlacementAttempt::class, 'placement_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(PlacementQuestion::class, 'placement_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(PlacementOption::class, 'placement_option_id');
    }
}
