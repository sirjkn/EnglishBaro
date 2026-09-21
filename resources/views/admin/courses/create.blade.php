<x-layouts.admin :title="'Add Course'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add Course</h1>

    <form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data" class="mt-6 max-w-3xl">
        @csrf
        @include('admin.courses._form', ['course' => null])

        <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Course</button>
    </form>
</x-layouts.admin>
