<x-layouts.admin :title="'Edit Student'">
    <a href="{{ \App\Support\SafeRedirect::resolve($redirectTo, route('admin.students.show', $student)) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">&larr; Back to Profile</a>
    <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Edit Student</h1>

    <form method="POST" action="{{ route('admin.students.update', $student) }}" class="mt-6 max-w-2xl" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @if ($redirectTo)
            <input type="hidden" name="redirect" value="{{ $redirectTo }}">
        @endif

        @include('admin.students._edit-fields', ['student' => $student, 'tracks' => $tracks, 'countries' => $countries])

        <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
    </form>
</x-layouts.admin>
