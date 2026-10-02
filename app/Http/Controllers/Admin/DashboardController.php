<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = [
            'total_students' => User::query()->where('user_type', 'student')->count(),
            'active_students' => User::query()->where('user_type', 'student')->where('is_active', true)->count(),
            'total_tracks' => Track::query()->count(),
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

        $earliestStudentYear = User::query()->where('user_type', 'student')->min('created_at');
        $earliestYear = $earliestStudentYear ? (int) \Illuminate\Support\Carbon::parse($earliestStudentYear)->format('Y') : now()->year;

        $selectedYear = (int) ($request->integer('year') ?: now()->year);
        $selectedMonthNum = (int) ($request->integer('month') ?: now()->month);
        $selectedMonthNum = max(1, min(12, $selectedMonthNum));

        $monthStart = \Illuminate\Support\Carbon::create($selectedYear, $selectedMonthNum, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $daysInMonth = $monthStart->daysInMonth;
        $selectedMonth = $monthStart->format('Y-m');

        $studentsByDay = User::query()
            ->where('user_type', 'student')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $yearOptions = collect(range(now()->year, $earliestYear))->values();

        $monthOptions = collect(range(1, 12))
            ->mapWithKeys(fn ($m) => [$m => \Illuminate\Support\Carbon::create($selectedYear, $m, 1)->format('F')]);

        $trackPopularity = Track::query()
            ->withCount(['enrollments' => fn ($query) => $query->where('status', 'active')])
            ->orderByDesc('enrollments_count')
            ->take(5)
            ->get(['id', 'name']);

        $paymentStatusBreakdown = Payment::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'stats' => $stats,
            'revenueByDay' => $this->fillDateRange($revenueByDay, 14),
            'studentsByDay' => $this->fillMonthRange($studentsByDay, $monthStart, $daysInMonth),
            'selectedMonth' => $selectedMonth,
            'selectedMonthLabel' => $monthStart->format('F Y'),
            'selectedYear' => $selectedYear,
            'selectedMonthNum' => $selectedMonthNum,
            'monthOptions' => $monthOptions,
            'yearOptions' => $yearOptions,
            'trackPopularity' => $trackPopularity,
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

    /**
     * Fills in every day of the selected month (up to today, if it's the
     * current month) with 0 where there's no data, so the chart always
     * draws a full month of x-axis labels.
     */
    private function fillMonthRange($data, \Illuminate\Support\Carbon $monthStart, int $daysInMonth): array
    {
        $result = [];

        for ($i = 0; $i < $daysInMonth; $i++) {
            $date = $monthStart->copy()->addDays($i)->format('Y-m-d');
            $result[$date] = (float) ($data[$date] ?? 0);
        }

        return $result;
    }
}
