<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HelpController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();
        $track = $user->studentProfile?->track;

        $enrollments = $user->enrollments()->with(['lastViewedLesson.section.level', 'subscription'])->get();

        $currentEnrollment = $track
            ? $enrollments->firstWhere('track_id', $track->id)
            : null;

        // All tracks are unlocked by the same single payment, so there's one
        // subscription clock for the whole course rather than one per track.
        $subscription = $enrollments
            ->pluck('subscription')
            ->filter(fn ($sub) => $sub && $sub->status === 'active')
            ->sortBy('expires_at')
            ->first();

        $helpVideoId = CompanySetting::get('help_video_media_id');
        $helpPhone = preg_replace('/\D+/', '', CompanySetting::get('phone', ''));

        return view('student.help', [
            'helpVideo' => $helpVideoId ? Media::find($helpVideoId) : null,
            'whatsappHref' => $helpPhone ? "https://wa.me/{$helpPhone}" : route('contact'),
            'track' => $track,
            'currentLevelNumber' => $currentEnrollment?->lastViewedLesson?->section?->level?->number ?? 1,
            'subscription' => $subscription,
        ]);
    }
}
