<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        $earliestStudentDate = User::query()->where('user_type', 'student')->min('created_at');
        $earliestPaymentDate = Payment::query()->where('status', 'successful')->min('created_at');

        $students = $this->monthSelection($request, 'year', 'month', $earliestStudentDate);
        $revenue = $this->monthSelection($request, 'revenue_year', 'revenue_month', $earliestPaymentDate);

        $studentsByDay = User::query()
            ->where('user_type', 'student')
            ->whereBetween('created_at', [$students['start'], $students['end']])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $revenueByDay = Payment::query()
            ->where('status', 'successful')
            ->whereBetween('created_at', [$revenue['start'], $revenue['end']])
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $monthNames = collect(range(1, 12))
            ->mapWithKeys(fn ($m) => [$m => Carbon::create(2000, $m, 1)->format('F')]);

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
            'monthNames' => $monthNames,

            'studentsByDay' => $this->fillMonthRange($studentsByDay, $students['start'], $students['daysInMonth']),
            'selectedMonthLabel' => $students['label'],
            'selectedYear' => $students['year'],
            'selectedMonthNum' => $students['month'],
            'yearOptions' => $students['yearOptions'],

            'revenueByDay' => $this->fillMonthRange($revenueByDay, $revenue['start'], $revenue['daysInMonth']),
            'selectedRevenueMonthLabel' => $revenue['label'],
            'selectedRevenueYear' => $revenue['year'],
            'selectedRevenueMonthNum' => $revenue['month'],
            'revenueYearOptions' => $revenue['yearOptions'],

            'trackPopularity' => $trackPopularity,
            'paymentStatusBreakdown' => $paymentStatusBreakdown,
        ]);
    }

    /**
     * Resolves the year/month a chart's filter is set to from the request
     * (defaulting to the current month), and everything needed to query and
     * render a full month of that chart's data.
     *
     * @return array{year: int, month: int, start: Carbon, end: Carbon, daysInMonth: int, label: string, yearOptions: \Illuminate\Support\Collection<int, int>}
     */
    private function monthSelection(Request $request, string $yearKey, string $monthKey, ?string $earliestDate): array
    {
        $earliestYear = $earliestDate ? (int) Carbon::parse($earliestDate)->format('Y') : now()->year;

        $year = (int) ($request->integer($yearKey) ?: now()->year);
        $month = max(1, min(12, (int) ($request->integer($monthKey) ?: now()->month)));

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return [
            'year' => $year,
            'month' => $month,
            'start' => $start,
            'end' => $end,
            'daysInMonth' => $start->daysInMonth,
            'label' => $start->format('F Y'),
            'yearOptions' => collect(range(now()->year, $earliestYear))->values(),
        ];
    }

    /**
     * Fills in every day of the selected month with 0 where there's no data,
     * so the chart always draws a full month of x-axis labels.
     */
    private function fillMonthRange($data, Carbon $monthStart, int $daysInMonth): array
    {
        $result = [];

        for ($i = 0; $i < $daysInMonth; $i++) {
            $date = $monthStart->copy()->addDays($i)->format('Y-m-d');
            $result[$date] = (float) ($data[$date] ?? 0);
        }

        return $result;
    }
}
