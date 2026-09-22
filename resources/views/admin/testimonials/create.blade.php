<x-layouts.admin :title="'Add Testimonial'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add Testimonial</h1>

    <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data" class="mt-6 max-w-lg">
        @csrf
        @include('admin.testimonials._form', ['testimonial' => null])

        <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Testimonial</button>
    </form>
</x-layouts.admin>
