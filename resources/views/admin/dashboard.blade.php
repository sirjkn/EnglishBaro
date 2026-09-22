<x-layouts.admin :title="'Dashboard'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat-card label="Total Students" :value="$stats['total_students']" />
        <x-stat-card label="Active Students" :value="$stats['active_students']" />
        <x-stat-card label="Total Courses" :value="$stats['total_courses']" />
        <x-stat-card label="Active Enrollments" :value="$stats['active_enrollments']" />
        <x-stat-card label="Revenue" :value="'$'.number_format($stats['revenue'], 2)" />
        <x-stat-card label="Pending Payments" :value="$stats['pending_payments']" />
        <x-stat-card label="Active Sessions" :value="$stats['active_sessions']" />
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Revenue (Last 14 Days)</h2>
            <canvas id="revenueChart" height="180"></canvas>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">New Students (Last 14 Days)</h2>
            <canvas id="studentsChart" height="180"></canvas>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Course Popularity</h2>
            <canvas id="courseChart" height="180"></canvas>
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
        const courseLabels = @json($coursePopularity->pluck('title'));
        const courseValues = @json($coursePopularity->pluck('enrollments_count'));
        const paymentLabels = @json($paymentStatusBreakdown->keys());
        const paymentValues = @json($paymentStatusBreakdown->values());

        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: { labels: Object.keys(revenueData), datasets: [{ label: 'Revenue', data: Object.values(revenueData), borderColor: '#0084FF', backgroundColor: 'rgba(0,132,255,0.12)', fill: true, tension: 0.3 }] },
            options: { plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('studentsChart'), {
            type: 'bar',
            data: { labels: Object.keys(studentsData), datasets: [{ label: 'New Students', data: Object.values(studentsData), backgroundColor: '#0084FF' }] },
            options: { plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('courseChart'), {
            type: 'bar',
            data: { labels: courseLabels, datasets: [{ label: 'Active Enrollments', data: courseValues, backgroundColor: '#f59e0b' }] },
            options: { indexAxis: 'y', plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('paymentChart'), {
            type: 'doughnut',
            data: { labels: paymentLabels, datasets: [{ data: paymentValues, backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#0084FF', '#9ca3af'] }] },
        });
    </script>
    @endpush
</x-layouts.admin>
