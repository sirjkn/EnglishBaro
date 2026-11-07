<x-layouts.admin :title="'Testimonials'">
    <div
        class="mt-4 grid grid-cols-1 gap-6 lg:grid-cols-4"
        x-data="{ selected: {{ $openId ?? 'null' }}, mode: '{{ $openId ? 'edit' : 'create' }}' }"
    >
        {{-- 1/4: Add Testimonial, testimonial list --}}
        <div class="lg:col-span-1">
            <button
                type="button"
                @click="mode = 'create'; selected = null"
                class="block w-full rounded-md bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-2 text-center text-sm font-semibold text-white shadow-sm hover:from-indigo-500 hover:to-indigo-400"
            >
                + Add Testimonial
            </button>

            <div class="mt-4 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="bg-indigo-600 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-white dark:bg-indigo-800">
                    Testimonials
                </div>
                <ul class="max-h-[60vh] divide-y divide-gray-100 overflow-y-auto dark:divide-gray-700">
                    @forelse ($testimonials as $testimonial)
                        <li>
                            <button
                                type="button"
                                @click="mode = 'edit'; selected = {{ $testimonial->id }}"
                                class="w-full px-3 py-2.5 text-left transition"
                                :class="(mode === 'edit' && selected === {{ $testimonial->id }}) ? 'bg-yellow-100 dark:bg-yellow-900/40' : 'bg-white hover:bg-yellow-100 dark:bg-gray-800 dark:hover:bg-yellow-900/30'"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $testimonial->name }}</span>
                                    <x-badge :color="$testimonial->is_published ? 'green' : 'gray'">{{ $testimonial->is_published ? 'Published' : 'Unpublished' }}</x-badge>
                                </div>
                                <p class="mt-1 truncate text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $testimonial->role_label ?? 'No role' }} &middot; {{ $testimonial->rating ?? '—' }}/5
                                </p>
                            </button>
                        </li>
                    @empty
                        <li class="px-3 py-8 text-center text-xs text-gray-400">No testimonials found.</li>
                    @endforelse
                </ul>
            </div>

            <div class="mt-3">{{ $testimonials->links() }}</div>
        </div>

        {{-- 3/4: overview cards + inline create/edit panel --}}
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-stat-card label="All Testimonials" :value="$overview['total']" icon="medal" color="indigo" compact />
                <x-stat-card label="Average Rating" :value="number_format($overview['average_rating'], 1).' / 5'" icon="star" color="amber" compact />
                <x-stat-card label="Published vs Unpublished" :value="$overview['published'].' / '.$overview['unpublished']" icon="user-check" color="emerald" compact />
            </div>

            <div class="mt-6">
                {{-- Create panel --}}
                <div x-show="mode === 'create'" x-cloak>
                    <div class="rounded-t-xl bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                        <h2 class="text-sm font-bold text-white">Add a new testimonial</h2>
                    </div>
                    <div class="max-h-[70vh] overflow-y-auto rounded-b-xl border border-gray-200 p-4 dark:border-gray-700">
                        <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data">
                            @csrf
                            @include('admin.testimonials._form', ['testimonial' => null])
                            <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Testimonial</button>
                        </form>
                    </div>
                </div>

                {{-- Edit panels, one per testimonial on this page --}}
                @foreach ($testimonials as $testimonial)
                    <div x-show="mode === 'edit' && selected === {{ $testimonial->id }}" x-cloak>
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-t-xl bg-indigo-600 px-4 py-3 dark:bg-indigo-800">
                            <div class="flex items-center gap-3">
                                <h2 class="text-sm font-bold text-white">Editing: {{ $testimonial->name }}</h2>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold text-white {{ $testimonial->is_published ? 'bg-green-500' : 'bg-orange-500' }}">{{ $testimonial->is_published ? 'Published' : 'Unpublished' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-confirm-action-form
                                    :action="route('admin.testimonials.toggle-publish', $testimonial)"
                                    :label="$testimonial->is_published ? 'Unpublish' : 'Publish'"
                                    :title="($testimonial->is_published ? 'Unpublish' : 'Publish').' '.$testimonial->name.'\'s testimonial?'"
                                    message="Any unsaved changes you've made to the fields below will be lost. Save your changes first if you want them included, then come back to publish or unpublish."
                                    :confirmLabel="$testimonial->is_published ? 'Yes, Unpublish' : 'Yes, Publish'"
                                    :class="'rounded-full px-3 py-1 text-xs font-medium text-white '.($testimonial->is_published ? 'border border-gray-500 bg-gray-400 hover:bg-gray-500' : 'border border-white/40 hover:bg-white/10')"
                                    :confirmClass="$testimonial->is_published ? 'rounded-md bg-gray-500 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600' : 'rounded-md bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-400'"
                                />
                                <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Delete this testimonial?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full border border-red-600 bg-red-500 px-3 py-1 text-xs font-semibold text-white hover:bg-red-400">Delete</button>
                                </form>
                            </div>
                        </div>
                        <div class="max-h-[70vh] overflow-y-auto rounded-b-xl border border-gray-200 p-4 dark:border-gray-700">
                            <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                @include('admin.testimonials._form', ['testimonial' => $testimonial])
                                <button type="submit" class="mt-6 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Changes</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.admin>
