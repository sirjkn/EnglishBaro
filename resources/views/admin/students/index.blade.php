<x-layouts.admin :title="'Students'">
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    @if (session('newPassword'))
        <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
            <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">New password generated — share this with the student now, it won't be shown again:</p>
            <p class="mt-1 select-all rounded bg-white px-3 py-2 font-mono text-sm text-gray-900 dark:bg-gray-900 dark:text-white">{{ session('newPassword') }}</p>
        </div>
    @endif

    <div
        class="mt-4 grid grid-cols-1 gap-6 lg:grid-cols-4"
        x-data="{ selected: {{ $openStudentId ?? 'null' }}, mode: '{{ $openStudentId ? 'edit' : 'create' }}' }"
    >
        {{-- 1/4: overview, Add Student, student list --}}
        <div class="lg:col-span-1">
            <div class="grid grid-cols-3 gap-2">
                <x-stat-card label="Total Students" :value="$overview['total']" compact class="!p-2" label-size="text-[9px]" />
                <x-stat-card label="Active Students" :value="$overview['active']" compact class="!p-2" label-size="text-[9px]" />
                <x-stat-card label="New This Month" :value="$overview['new_this_month']" compact class="!p-2" label-size="text-[9px]" />
            </div>

            <button
                type="button"
                @click="mode = 'create'; selected = null"
                class="mt-4 block w-full rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-center text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400"
            >
                + Add Student
            </button>

            <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="bg-indigo-600 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-white dark:bg-indigo-800">
                    Students
                </div>
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($students as $student)
                        <li>
                            <button
                                type="button"
                                @click="mode = 'edit'; selected = {{ $student->id }}"
                                class="w-full px-3 py-2.5 text-left transition"
                                :class="(mode === 'edit' && selected === {{ $student->id }}) ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700'"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $student->name }}</span>
                                    <x-badge :color="$student->is_active ? 'green' : 'gray'">{{ $student->is_active ? 'Active' : 'Inactive' }}</x-badge>
                                </div>
                                <p class="mt-1 truncate text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $student->studentProfile?->student_id }} &middot; {{ $student->studentProfile?->track?->track_code ?? 'No track' }} &middot; {{ $student->enrollments_count }} enroll.
                                </p>
                            </button>
                        </li>
                    @empty
                        <li class="px-3 py-8 text-center text-xs text-gray-400">No students found.</li>
                    @endforelse
                </ul>
            </div>

            <div class="mt-3">{{ $students->links() }}</div>
        </div>

        {{-- 3/4: filters + inline create/edit panel --}}
        <div class="lg:col-span-3">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Students</h1>

            <form method="GET" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-4">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, email, student ID..." class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <select name="track" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">All Tracks</option>
                    @foreach ($tracks as $track)
                        <option value="{{ $track->id }}" @selected(($filters['track'] ?? null) == $track->id)>{{ $track->track_code }} — {{ $track->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200">Filter</button>
            </form>

            <div class="mt-6">
                {{-- Create panel --}}
                <div x-show="mode === 'create'" x-cloak>
                    <div class="rounded-t-xl bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                        <h2 class="text-sm font-bold text-white">Add a new student</h2>
                    </div>
                    <div class="rounded-b-xl border border-gray-200 p-4 dark:border-gray-700">
                        <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
                            @csrf
                            @include('admin.students._create-fields', ['countries' => $countries])
                            <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Student</button>
                        </form>
                    </div>
                </div>

                {{-- Edit panels, one per student on this page --}}
                @forelse ($students as $student)
                    <div x-show="mode === 'edit' && selected === {{ $student->id }}" x-cloak>
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-t-xl bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                            <h2 class="text-sm font-bold text-white">Editing: {{ $student->name }}</h2>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.students.show', $student) }}" class="text-xs font-medium text-indigo-100 hover:text-white">Full Profile</a>
                                <form method="POST" action="{{ route('admin.students.destroy', $student) }}" class="inline" onsubmit="return confirm('Remove this student? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-red-200 hover:text-white">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div class="rounded-b-xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="grid grid-cols-1 gap-8 xl:grid-cols-2">
                                {{-- Bio data --}}
                                <div>
                                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/40">
                                        <div class="flex items-center gap-2 text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">Email Verified:</span>
                                            <x-badge :color="$student->hasVerifiedEmail() ? 'green' : 'red'" dot>{{ $student->hasVerifiedEmail() ? 'Verified' : 'Not Verified' }}</x-badge>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @unless ($student->hasVerifiedEmail())
                                                <form method="POST" action="{{ route('admin.students.verify-email', $student) }}" onsubmit="return confirm('Mark {{ $student->name }}\'s email as verified? Use this if their verification email never arrived or the link isn\'t working.');">
                                                    @csrf
                                                    <button type="submit" class="rounded-md border border-emerald-300 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-300 dark:hover:bg-emerald-900/30">Verify Email</button>
                                                </form>
                                            @endunless
                                            <form method="POST" action="{{ route('admin.students.reset-password', $student) }}" onsubmit="return confirm('Reset {{ $student->name }}\'s password? A new one will be generated and shown once.');">
                                                @csrf
                                                <button type="submit" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Reset Password</button>
                                            </form>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="redirect" value="{{ request()->fullUrlWithQuery(['open' => $student->id]) }}">
                                        @include('admin.students._edit-fields', ['student' => $student, 'tracks' => $tracks, 'countries' => $countries])
                                        <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
                                    </form>
                                </div>

                                {{-- Track --}}
                                <div>
                                    <h2 class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                                            <x-icons.book-open class="h-4 w-4" />
                                        </span>
                                        Track
                                    </h2>
                                    @if ($student->enrollments->isEmpty())
                                        <x-empty-state class="mt-4" message="No enrollments yet." />
                                    @else
                                        <div class="mt-4 space-y-3">
                                            @foreach ($student->enrollments as $enrollment)
                                                @php
                                                    $cancelMessage = "This revokes {$student->name}'s access to {$enrollment->track->track_code}. Their payment record is kept — this does not refund them.";
                                                @endphp
                                                <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 text-sm dark:bg-gray-900">
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">{{ $enrollment->track->track_code }} — {{ $enrollment->track->name }}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            Enrolled {{ $enrollment->enrolled_at->format('M d, Y') }}
                                                            @if ($enrollment->subscription)
                                                                &middot; Expires {{ $enrollment->subscription->expires_at->format('M d, Y') }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <x-badge :color="$enrollment->status === 'active' ? 'green' : 'gray'" dot>{{ ucfirst($enrollment->status) }}</x-badge>
                                                        @if ($enrollment->status === 'active')
                                                            <x-double-confirm-form
                                                                :action="route('admin.enrollments.cancel', $enrollment)"
                                                                label="Cancel"
                                                                title="Cancel this enrollment?"
                                                                :message="$cancelMessage"
                                                                class="text-xs font-medium text-red-600 hover:text-red-500"
                                                            />
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-8 grid grid-cols-1 gap-8 border-t border-gray-100 pt-6 dark:border-gray-700 xl:grid-cols-2">
                                {{-- Active Sessions --}}
                                <div>
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <h2 class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                                                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                                                    <x-icons.login class="h-4 w-4" />
                                                </span>
                                                Active Sessions
                                            </h2>
                                            @php $sessionsThisMonth = $sessionsThisMonthByStudentId[$student->id] ?? 0; @endphp
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                Sessions used this month: <span class="font-semibold {{ $sessionsThisMonth >= $sessionMonthlyLimit ? 'text-red-600' : 'text-gray-700 dark:text-gray-300' }}">{{ $sessionsThisMonth }} / {{ $sessionMonthlyLimit }}</span>
                                            </p>
                                        </div>
                                        <form method="POST" action="{{ route('admin.students.reset-session-quota', $student) }}" onsubmit="return confirm('Reset this student\'s session quota for the current month?');">
                                            @csrf
                                            <button type="submit" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">Reset Session Quota</button>
                                        </form>
                                    </div>
                                    <div class="mt-4 overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                                            <thead>
                                                <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                                                    <th class="py-2 pr-4">Device</th>
                                                    <th class="py-2 pr-4">Browser</th>
                                                    <th class="py-2 pr-4">IP</th>
                                                    <th class="py-2 pr-4">Last Activity</th>
                                                    <th class="py-2 pr-4">Status</th>
                                                    <th class="py-2"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                                                @foreach ($student->userSessions as $session)
                                                    <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                                        <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->device }}</td>
                                                        <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->browser }}</td>
                                                        <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->ip_address }}</td>
                                                        <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $session->last_activity_at?->diffForHumans() }}</td>
                                                        <td class="py-2 pr-4"><x-badge :color="$session->status === 'active' ? 'green' : 'gray'" dot>{{ ucfirst($session->status) }}</x-badge></td>
                                                        <td class="py-2 text-right">
                                                            @if ($session->status === 'active')
                                                                <form method="POST" action="{{ route('admin.sessions.destroy', $session) }}" onsubmit="return confirm('Terminate this session?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">Terminate</button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Payments --}}
                                <div>
                                    <h2 class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                                            <x-icons.credit-card class="h-4 w-4" />
                                        </span>
                                        Payments
                                    </h2>
                                    @if ($student->payments->isEmpty())
                                        <x-empty-state class="mt-4" message="No payment history yet." />
                                    @else
                                        <div class="mt-4 overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
                                                <thead>
                                                    <tr class="bg-indigo-600 text-left text-xs uppercase text-white dark:bg-indigo-800 dark:text-white divide-x divide-white">
                                                        <th class="py-2 pr-4">Transaction</th>
                                                        <th class="py-2 pr-4">Track</th>
                                                        <th class="py-2 pr-4">Amount</th>
                                                        <th class="py-2 pr-4">Method</th>
                                                        <th class="py-2 pr-4">Status</th>
                                                        <th class="py-2">Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                                                    @foreach ($student->payments as $payment)
                                                        <tr class="odd:bg-white even:bg-indigo-50 dark:odd:bg-gray-800 dark:even:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700">
                                                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->transaction_id }}</td>
                                                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->track->track_code }}</td>
                                                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                                                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $payment->payment_method ? ucfirst(str_replace('_', ' ', $payment->payment_method)) : '—' }}</td>
                                                            <td class="py-2 pr-4"><x-payment-status :status="$payment->status" /></td>
                                                            <td class="py-2 text-gray-700 dark:text-gray-300">{{ $payment->created_at->format('M d, Y') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
