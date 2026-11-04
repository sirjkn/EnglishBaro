<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAnswer;
use App\Services\AssessmentGradingService;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnswerReviewController extends Controller
{
    public function index(): View
    {
        $answers = AssessmentAnswer::query()
            ->where('review_status', AssessmentAnswer::REVIEW_PENDING)
            ->with([
                'attempt.user',
                'question.assessment.section.level.track',
            ])
            ->latest('created_at')
            ->paginate(20);

        $overview = [
            'pending' => AssessmentAnswer::query()->where('review_status', AssessmentAnswer::REVIEW_PENDING)->count(),
            'approved' => AssessmentAnswer::query()->where('review_status', AssessmentAnswer::REVIEW_APPROVED)->count(),
            'rejected' => AssessmentAnswer::query()->where('review_status', AssessmentAnswer::REVIEW_REJECTED)->count(),
        ];

        return view('admin.answer-reviews.index', [
            'answers' => $answers,
            'overview' => $overview,
        ]);
    }

    public function update(Request $request, AssessmentAnswer $assessmentAnswer, AssessmentGradingService $grading): RedirectResponse
    {
        abort_unless($assessmentAnswer->question->type === 'short_answer', 404);

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $grading->reviewShortAnswer(
            $assessmentAnswer,
            $validated['decision'] === 'approve',
            $validated['remarks'] ?? null,
            Auth::user()
        );

        AuditLogger::log('admin.answer_review.decided', $assessmentAnswer, [], $validated);

        return back()->with('status', 'Answer '.($validated['decision'] === 'approve' ? 'approved' : 'rejected').'.');
    }
}
