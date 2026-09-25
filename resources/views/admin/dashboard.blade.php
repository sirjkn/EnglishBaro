<x-layouts.admin :title="'Dashboard'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat-card label="Total Students" :value="$stats['total_students']" icon="users" icon-bg="bg-indigo-500 text-white" blob="bg-indigo-500/10" />
        <x-stat-card label="Active Students" :value="$stats['active_students']" icon="user-check" icon-bg="bg-emerald-500 text-white" blob="bg-emerald-500/10" />
        <x-stat-card label="Total Tracks" :value="$stats['total_tracks']" icon="book-open" icon-bg="bg-purple-500 text-white" blob="bg-purple-500/10" />
        <x-stat-card label="Active Enrollments" :value="$stats['active_enrollments']" icon="clipboard-check" icon-bg="bg-amber-500 text-white" blob="bg-amber-500/10" />
        <x-stat-card label="Revenue" :value="'$'.number_format($stats['revenue'], 2)" icon="currency-dollar" icon-bg="bg-teal-500 text-white" blob="bg-teal-500/10" />
        <x-stat-card label="Pending Payments" :value="$stats['pending_payments']" icon="clock" icon-bg="bg-rose-500 text-white" blob="bg-rose-500/10" />
        <x-stat-card label="Active Sessions" :value="$stats['active_sessions']" icon="bolt" icon-bg="bg-cyan-500 text-white" blob="bg-cyan-500/10" />
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

        new Chart(document.getElementById('studentsChart'), {
            type: 'bar',
            data: { labels: Object.keys(studentsData), datasets: [{ label: 'New Students', data: Object.values(studentsData), backgroundColor: '#0084FF' }] },
            options: { plugins: { legend: { display: false } } }
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
