@props(['chart', 'filename'])

<div x-data="{ open: false }" class="relative">
    <button
        type="button"
        @click="open = !open"
        @click.outside="open = false"
        class="flex items-center gap-1.5 rounded-md border border-gray-300 px-2.5 py-1.5 text-xs font-medium text-gray-600 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
    >
        <x-icons.download class="h-3.5 w-3.5" />
        Export
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition
        class="absolute right-0 z-20 mt-1 w-40 rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800"
    >
        <button
            type="button"
            @click="window.exportChartAsPdf('{{ $chart }}', '{{ $filename }}'); open = false"
            class="block w-full px-3 py-2 text-left text-xs text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700"
        >
            Export as PDF
        </button>
        <button
            type="button"
            @click="window.exportChartAsExcel('{{ $chart }}', '{{ $filename }}'); open = false"
            class="block w-full px-3 py-2 text-left text-xs text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700"
        >
            Export as Excel (.xlsx)
        </button>
    </div>
</div>
