<?php

namespace App\Services;

use App\Models\PlacementAnswer;
use App\Models\PlacementAttempt;
use App\Models\PlacementOption;
use App\Models\PlacementQuestion;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PlacementTestService
{
    public function hasCompletedPlacement(User $user): bool
    {
        return $user->studentProfile?->track_id !== null;
    }

    public function questions(): Collection
    {
        return PlacementQuestion::query()->with('options.track')->orderBy('order')->get();
    }

    /**
     * @param  array<int, int>  $answers  [question_id => option_id]
     */
    public function submit(User $user, array $answers): PlacementAttempt
    {
        return DB::transaction(function () use ($user, $answers) {
            $options = PlacementOption::query()->whereIn('id', array_values($answers))->get()->keyBy('id');

            $tally = [];
            foreach ($options as $option) {
                $tally[$option->track_id] = ($tally[$option->track_id] ?? 0) + 1;
            }

            $placedTrackId = $this->pickWinningTrack($tally);

            $attempt = PlacementAttempt::create([
                'user_id' => $user->id,
                'track_id' => $placedTrackId,
                'completed_at' => now(),
            ]);

            foreach ($answers as $questionId => $optionId) {
                PlacementAnswer::create([
                    'placement_attempt_id' => $attempt->id,
                    'placement_question_id' => $questionId,
                    'placement_option_id' => $optionId,
                ]);
            }

            $user->studentProfile?->update(['track_id' => $placedTrackId]);

            return $attempt;
        });
    }

    /**
     * @param  array<int, int>  $tally  [track_id => count]
     */
    private function pickWinningTrack(array $tally): int
    {
        $maxCount = max($tally);
        $winningTrackIds = array_keys(array_filter($tally, fn ($count) => $count === $maxCount));

        // Ties are broken conservatively: place the student into the easier track.
        return Track::query()->whereIn('id', $winningTrackIds)->orderBy('order')->value('id');
    }
}
