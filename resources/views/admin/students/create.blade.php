<x-layouts.admin :title="'Add Student'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add Student</h1>

    <form method="POST" action="{{ route('admin.students.store') }}" class="mt-6 max-w-lg" enctype="multipart/form-data">
        @csrf

        @include('admin.students._create-fields', ['countries' => $countries])

        <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Student</button>
    </form>
</x-layouts.admin>
