<x-layouts.admin :title="'Dashboard'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat-card label="Total Students" :value="$stats['total_students']" icon="users" color="indigo" />
        <x-stat-card label="Active Students" :value="$stats['active_students']" icon="user-check" color="emerald" />
        <x-stat-card label="Total Tracks" :value="$stats['total_tracks']" icon="book-open" color="purple" />
        <x-stat-card label="Active Enrollments" :value="$stats['active_enrollments']" icon="clipboard-check" color="amber" />
        <x-stat-card label="Revenue" :value="'$'.number_format($stats['revenue'], 2)" icon="currency-dollar" color="teal" />
        <x-stat-card label="Pending Payments" :value="$stats['pending_payments']" icon="clock" color="rose" />
        <x-stat-card label="Active Sessions" :value="$stats['active_sessions']" icon="bolt" color="cyan" />
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Revenue (Last 14 Days)</h2>
            <canvas id="revenueChart" height="180"></canvas>
        </div>
        <div class="flex h-full flex-col rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">New Students ({{ $selectedMonthLabel }})</h2>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <select name="year" onchange="this.form.submit()" class="rounded-md border-gray-300 text-xs font-medium text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected($year === $selectedYear)>{{ $year }}</option>
                        @endforeach
                    </select>
                    <select name="month" onchange="this.form.submit()" class="rounded-md border-gray-300 text-xs font-medium text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                        @foreach ($monthOptions as $value => $label)
                            <option value="{{ $value }}" @selected($value === $selectedMonthNum)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="mt-2 min-h-[180px] flex-1">
                <canvas id="studentsChart"></canvas>
            </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Track Popularity</h2>
            <canvas id="trackChart" height="180"></canvas>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Payment Status</h2>
            <canvas id="paymentChart" height="180"></canvas>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        const revenueData = @json($revenueByDay);
        const studentsData = @json($studentsByDay);
        const trackLabels = @json($trackPopularity->pluck('name'));
        const trackValues = @json($trackPopularity->pluck('enrollments_count'));
        const paymentLabels = @json($paymentStatusBreakdown->keys());
        const paymentValues = @json($paymentStatusBreakdown->values());

        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: { labels: Object.keys(revenueData), datasets: [{ label: 'Revenue', data: Object.values(revenueData), borderColor: '#0084FF', backgroundColor: 'rgba(0,132,255,0.12)', fill: true, tension: 0.3 }] },
            options: { plugins: { legend: { display: false } } }
        });

        const studentDayLabels = Object.keys(studentsData).map(day => parseInt(day.split('-')[2], 10));

        new Chart(document.getElementById('studentsChart'), {
            type: 'bar',
            data: { labels: studentDayLabels, datasets: [{ label: 'Students Enrolled', data: Object.values(studentsData), backgroundColor: '#0084FF' }] },
            options: {
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: (items) => `Day ${items[0].label}`,
                            label: (item) => `Total students enrolled: ${item.raw}`,
                        },
                    },
                },
                scales: {
                    y: { min: 0, max: 20, ticks: { stepSize: 5 } },
                    x: { ticks: { autoSkip: false, maxRotation: 0, minRotation: 0, font: { size: 9 } } },
                },
            }
        });

        new Chart(document.getElementById('trackChart'), {
            type: 'bar',
            data: { labels: trackLabels, datasets: [{ label: 'Active Enrollments', data: trackValues, backgroundColor: '#f59e0b' }] },
            options: { indexAxis: 'y', plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('paymentChart'), {
            type: 'doughnut',
            data: { labels: paymentLabels, datasets: [{ data: paymentValues, backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#0084FF', '#9ca3af'] }] },
        });
    </script>
    @endpush
</x-layouts.admin>
