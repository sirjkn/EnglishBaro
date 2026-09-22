<x-layouts.admin :title="'Edit Testimonial'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Testimonial</h1>

    <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data" class="mt-6 max-w-lg">
        @csrf
        @method('PUT')
        @include('admin.testimonials._form', ['testimonial' => $testimonial])

        <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
    </form>
</x-layouts.admin>
