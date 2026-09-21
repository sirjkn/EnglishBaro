<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total_students' => User::query()->where('user_type', 'student')->count(),
            'active_students' => User::query()->where('user_type', 'student')->where('is_active', true)->count(),
            'total_courses' => Course::query()->count(),
            'active_enrollments' => Enrollment::query()->where('status', 'active')->count(),
            'revenue' => (float) Payment::query()->where('status', 'successful')->sum('amount'),
            'pending_payments' => Payment::query()->whereIn('status', ['pending', 'processing'])->count(),
            'active_sessions' => UserSession::query()->where('status', 'active')->count(),
        ];

        $revenueByDay = Payment::query()
            ->where('status', 'successful')
            ->where('created_at', '>=', now()->subDays(13))
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $studentsByDay = User::query()
            ->where('user_type', 'student')
            ->where('created_at', '>=', now()->subDays(13))
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $coursePopularity = Course::query()
            ->withCount(['enrollments' => fn ($query) => $query->where('status', 'active')])
            ->orderByDesc('enrollments_count')
            ->take(5)
            ->get(['id', 'title']);

        $paymentStatusBreakdown = Payment::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'stats' => $stats,
            'revenueByDay' => $this->fillDateRange($revenueByDay, 14),
            'studentsByDay' => $this->fillDateRange($studentsByDay, 14),
            'coursePopularity' => $coursePopularity,
            'paymentStatusBreakdown' => $paymentStatusBreakdown,
        ]);
    }

    private function fillDateRange($data, int $days): array
    {
        $result = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $result[$date] = (float) ($data[$date] ?? 0);
        }

        return $result;
    }
}
