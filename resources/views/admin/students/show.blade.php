<x-layouts.admin :title="$student->name">
    @php
        $initials = collect(explode(' ', trim($student->name)))
            ->filter()
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('');

        $avatarPalettes = [
            'bg-indigo-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-sky-500', 'bg-purple-500',
        ];
        $avatarColor = $avatarPalettes[crc32($student->email) % count($avatarPalettes)];

        $nearestExpiry = $student->enrollments
            ->map(fn ($enrollment) => $enrollment->subscription)
            ->filter(fn ($subscription) => $subscription && $subscription->status === 'active')
            ->sortBy('expires_at')
            ->first();

        $latestSession = $student->userSessions->sortByDesc('last_activity_at')->first();

        $successfulPayments = $student->payments->where('status', 'successful');
        $totalPaid = $successfulPayments->sum('amount');
        $paidCurrency = $successfulPayments->first()?->currency ?? '';
    @endphp

    <div class="relative overflow-hidden rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-100 p-6 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800">
        <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-200/50 dark:bg-blue-900/20"></div>

        <div class="relative flex flex-col items-start gap-4 pr-20 sm:flex-row sm:items-center sm:justify-between sm:pr-0">
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                <x-student-avatar :profile="$student->studentProfile" size="h-20 w-20" class="border-4 border-white shadow-sm dark:border-gray-700" />
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $student->name }}</h1>
                    <p class="mt-0.5 text-sm font-semibold text-blue-700 dark:text-blue-300">{{ $student->studentProfile?->student_id ?? '—' }}</p>
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                        <x-icons.envelope class="h-4 w-4" /> {{ $student->email }}
                    </p>
                    <div class="mt-1.5">
                        <x-badge :color="$student->is_active ? 'green' : 'gray'" dot>{{ $student->is_active ? 'Active' : 'Inactive' }}</x-badge>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.students.edit', ['student' => $student, 'redirect' => request()->getRequestUri()]) }}" class="absolute right-0 top-0 flex shrink-0 items-center gap-1.5 rounded-md border border-blue-300 bg-white px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50 dark:border-gray-600 dark:bg-gray-800 dark:text-blue-300 dark:hover:bg-gray-700 sm:static">
                <x-icons.pencil class="h-4 w-4" /> Edit
            </a>
        </div>

        <div class="relative mt-5 flex flex-wrap items-center gap-2 border-t border-blue-200/60 pt-4 dark:border-gray-700">
            @if ($student->studentProfile?->track)
                <x-badge color="indigo">Current Track: {{ $student->studentProfile->track->track_code }}</x-badge>
            @endif
            <span class="text-xs text-gray-400">&middot;</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $student->enrollments->count() }} Enrollment{{ $student->enrollments->count() === 1 ? '' : 's' }}</span>
            <span class="text-xs text-gray-400">&middot;</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">Member since {{ $student->created_at->format('M Y') }}</span>
        </div>
    </div>

    @if (session('newPassword'))
        <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
            <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">New password generated — share this with {{ $student->name }} now, it won't be shown again:</p>
            <p class="mt-1 select-all rounded bg-white px-3 py-2 font-mono text-sm text-gray-900 dark:bg-gray-900 dark:text-white">{{ session('newPassword') }}</p>
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card
            label="Track Expires"
            :value="$nearestExpiry ? $nearestExpiry->expires_at->format('M j, Y') : 'N/A'"
            :sub="$nearestExpiry ? max(0, (int) floor(now()->diffInDays($nearestExpiry->expires_at, false))).' Days Left' : 'No active subscription'"
        />
        <x-stat-card
            label="Sessions This Month"
            :value="$sessionsThisMonth.' of '.$sessionMonthlyLimit"
            :sub="$latestSession ? $latestSession->device.', '.($latestSession->status === 'active' ? 'active now' : $latestSession->last_activity_at?->diffForHumans()) : 'No sessions yet'"
        />
        <x-stat-card
            label="Total Paid"
            :value="$paidCurrency.' '.number_format((float) $totalPaid, 2)"
            :sub="$successfulPayments->count().' successful payment'.($successfulPayments->count() === 1 ? '' : 's')"
        />
    </div>

    <p class="mt-6 text-sm text-gray-500 dark:text-gray-400">
        Track, Active Sessions, and Payments are shown in the inline edit panel on the
        <a href="{{ route('admin.students.index', ['open' => $student->id]) }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Students list</a>.
    </p>
</x-layouts.admin>
