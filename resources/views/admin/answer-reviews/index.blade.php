<x-layouts.admin :title="'Answer Reviews'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Answer Reviews</h1>
    <p class="mt-1 max-w-2xl text-sm text-gray-600 dark:text-gray-400">
        Short-answer questions can have multiple valid phrasings, so they aren't auto-graded strictly.
        Review each student's answer below and approve or reject it, with optional remarks.
    </p>

    <div class="mt-6 space-y-4">
        @forelse ($answers as $answer)
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium uppercase text-indigo-600 dark:text-indigo-400">
                            {{ $answer->question->assessment->section->level->track->track_code }}
                            &middot; Level {{ $answer->question->assessment->section->level->number }}
                            &middot; {{ $answer->question->assessment->section->title }}
                        </p>
                        <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $answer->question->question }}</p>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $answer->attempt->user->name }} &middot; {{ $answer->created_at->diffForHumans() }}
                    </span>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Student's answer</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $answer->short_answer_text ?: '—' }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Expected answer (guide only)</p>
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $answer->question->correct_short_answer ?: '—' }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.answer-reviews.update', $answer) }}" class="mt-4 space-y-2">
                    @csrf
                    @method('PUT')
                    <textarea name="remarks" rows="2" placeholder="Optional remarks for the student" class="w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                    <div class="flex gap-2">
                        <button type="submit" name="decision" value="approve" class="rounded-md bg-green-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-green-500">Approve</button>
                        <button type="submit" name="decision" value="reject" class="rounded-md bg-red-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-red-500">Reject</button>
                    </div>
                </form>
            </div>
        @empty
            <x-empty-state message="No answers waiting for review." />
        @endforelse
    </div>

    <div class="mt-4">{{ $answers->links() }}</div>
</x-layouts.admin>
