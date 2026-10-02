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
        <div class="flex h-full flex-col rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Revenue ({{ $selectedRevenueMonthLabel }})</h2>
                <div class="flex items-center gap-2">
                    <x-chart-export-menu chart="revenueChart" :filename="'revenue-'.\Illuminate\Support\Str::slug($selectedRevenueMonthLabel)" />
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <select name="revenue_year" onchange="this.form.submit()" class="rounded-md border-gray-300 text-xs font-medium text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            @foreach ($revenueYearOptions as $year)
                                <option value="{{ $year }}" @selected($year === $selectedRevenueYear)>{{ $year }}</option>
                            @endforeach
                        </select>
                        <select name="revenue_month" onchange="this.form.submit()" class="rounded-md border-gray-300 text-xs font-medium text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            @foreach ($monthNames as $value => $label)
                                <option value="{{ $value }}" @selected($value === $selectedRevenueMonthNum)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            <div class="mt-2 min-h-[180px] flex-1">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="flex h-full flex-col rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">New Students ({{ $selectedMonthLabel }})</h2>
                <div class="flex items-center gap-2">
                    <x-chart-export-menu chart="studentsChart" :filename="'new-students-'.\Illuminate\Support\Str::slug($selectedMonthLabel)" />
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <select name="year" onchange="this.form.submit()" class="rounded-md border-gray-300 text-xs font-medium text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected($year === $selectedYear)>{{ $year }}</option>
                            @endforeach
                        </select>
                        <select name="month" onchange="this.form.submit()" class="rounded-md border-gray-300 text-xs font-medium text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            @foreach ($monthNames as $value => $label)
                                <option value="{{ $value }}" @selected($value === $selectedMonthNum)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.2/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.4/dist/jspdf.plugin.autotable.min.js"></script>
    <script>
        const charts = {};

        /**
         * Chart labels are "day" / "value" pairs in whatever order the chart
         * draws them in — this reads them back out generically so one export
         * function works for the revenue line chart and the students bar
         * chart alike.
         */
        function chartRows(chartId) {
            const chart = charts[chartId];
            const dataset = chart.data.datasets[0];

            return chart.data.labels.map((day, i) => ({
                day: `Day ${day}`,
                label: dataset.label,
                value: dataset.data[i],
            }));
        }

        window.exportChartAsExcel = function (chartId, filename) {
            const rows = chartRows(chartId);
            const valueLabel = rows[0]?.label ?? 'Value';
            const sheetRows = rows.map((row) => ({ Day: row.day, [valueLabel]: row.value }));

            const worksheet = XLSX.utils.json_to_sheet(sheetRows);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Data');
            XLSX.writeFile(workbook, `${filename}.xlsx`);
        };

        window.exportChartAsPdf = function (chartId, filename) {
            const chart = charts[chartId];
            const rows = chartRows(chartId);
            const valueLabel = rows[0]?.label ?? 'Value';
            const title = document.getElementById(chartId).closest('.flex-col').querySelector('h2').textContent;

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

            const pageWidth = doc.internal.pageSize.getWidth(); // 297mm
            const margin = 12;
            const contentWidth = pageWidth - margin * 2;

            doc.setFontSize(14);
            doc.text(title, pageWidth / 2, 14, { align: 'center' });

            // Chart fills most of the page width, leaving room below for a
            // single compact row of day-by-day values instead of a tall list.
            const chartHeight = 110;
            doc.addImage(chart.toBase64Image(), 'PNG', margin, 20, contentWidth, chartHeight);

            doc.autoTable({
                startY: 20 + chartHeight + 6,
                margin: { left: margin, right: margin },
                tableWidth: contentWidth,
                theme: 'grid',
                head: [['Day', ...rows.map((row) => row.day.replace('Day ', ''))]],
                body: [[valueLabel, ...rows.map((row) => row.value)]],
                styles: { fontSize: 7, halign: 'center', cellPadding: 1.2, lineWidth: 0.1, lineColor: [180, 190, 210] },
                headStyles: { fillColor: [0, 132, 255], textColor: [255, 255, 255], halign: 'center', lineColor: [255, 255, 255] },
                columnStyles: { 0: { halign: 'left', fontStyle: 'bold', cellWidth: 26 } },
            });

            doc.save(`${filename}.pdf`);
        };

        const revenueData = @json($revenueByDay);
        const studentsData = @json($studentsByDay);
        const trackLabels = @json($trackPopularity->pluck('name'));
        const trackValues = @json($trackPopularity->pluck('enrollments_count'));
        const paymentLabels = @json($paymentStatusBreakdown->keys());
        const paymentValues = @json($paymentStatusBreakdown->values());

        const dayOfMonth = (day) => parseInt(day.split('-')[2], 10);
        const revenueDayLabels = Object.keys(revenueData).map(dayOfMonth);

        charts.revenueChart = new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: { labels: revenueDayLabels, datasets: [{ label: 'Revenue', data: Object.values(revenueData), borderColor: '#0084FF', backgroundColor: 'rgba(0,132,255,0.12)', fill: true, tension: 0.3 }] },
            options: {
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: (items) => `Day ${items[0].label}`,
                            label: (item) => `Revenue: $${item.raw}`,
                        },
                    },
                },
                scales: {
                    y: { min: 0, max: 200, ticks: { stepSize: 50 } },
                    x: { ticks: { autoSkip: false, maxRotation: 0, minRotation: 0, font: { size: 9 } } },
                },
            }
        });

        const studentDayLabels = Object.keys(studentsData).map(dayOfMonth);

        charts.studentsChart = new Chart(document.getElementById('studentsChart'), {
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
