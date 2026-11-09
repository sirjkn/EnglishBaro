@props(['lesson' => null])

<div class="rounded-lg border border-gray-200 dark:border-gray-700">
    <p class="border-b border-gray-200 px-3 py-2 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:border-gray-700">Media</p>

    <ul class="divide-y divide-gray-100 px-3 dark:divide-gray-700" x-show="existingResources.length" x-cloak>
        <template x-for="resource in existingResources" :key="resource.id">
            <li class="flex items-center justify-between gap-2 py-1.5 text-xs">
                <span class="flex min-w-0 items-center gap-1.5 text-gray-600 dark:text-gray-300">
                    <template x-if="resource.mtype === 'image' && resource.resolvedUrl">
                        <button type="button" @click="openLightbox(resource.resolvedUrl)" class="flex-none">
                            <img :src="resource.resolvedUrl" :alt="resource.title" class="h-7 w-7 rounded object-cover ring-1 ring-black/10 hover:opacity-80 dark:ring-white/10">
                        </button>
                    </template>
                    <template x-if="resource.mtype === 'video'">
                        <x-icons.play-circle class="h-3.5 w-3.5 flex-none text-indigo-500" />
                    </template>
                    <template x-if="resource.mtype === 'audio'">
                        <x-icons.music-note class="h-3.5 w-3.5 flex-none text-indigo-500" />
                    </template>
                    <template x-if="!['image', 'video', 'audio'].includes(resource.mtype)">
                        <x-icons.book-open class="h-3.5 w-3.5 flex-none text-indigo-500" />
                    </template>
                    <span class="truncate" x-text="resource.title"></span>
                </span>
                <button type="button" @click="removeExistingResource(resource)" :disabled="resource.removing"
                        class="flex-none text-gray-400 hover:text-red-600 disabled:opacity-40" title="Remove">&times;</button>
            </li>
        </template>
    </ul>

    @if ($lesson)
        <div x-init="existingResources = @js($lesson->resources->map(fn ($resource) => [
            'id' => $resource->id,
            'title' => $resource->title,
            'mtype' => $resource->media?->type,
            'resolvedUrl' => $resource->media?->resolved_url,
            'removing' => false,
        ]))"></div>
    @endif

    <ul class="space-y-1.5 px-3 pt-2" x-show="uploads.length" x-cloak>
        <template x-for="upload in uploads" :key="upload.key">
            <li class="flex items-center gap-2 rounded-md bg-gray-50 px-2 py-1.5 text-xs dark:bg-gray-900">
                <template x-if="upload.previewUrl">
                    <button type="button" @click="openLightbox(upload.previewUrl)" class="flex-none">
                        <img :src="upload.previewUrl" class="h-7 w-7 rounded object-cover ring-1 ring-black/10 hover:opacity-80 dark:ring-white/10">
                    </button>
                </template>
                <span class="min-w-0 flex-1 truncate text-gray-600 dark:text-gray-300" x-text="upload.name"></span>

                <template x-if="upload.status === 'uploading'">
                    <div class="h-1.5 w-16 flex-none overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-full bg-indigo-500 transition-all" :style="'width: ' + upload.progress + '%'"></div>
                    </div>
                </template>
                <span x-show="upload.status === 'uploading'" class="w-9 flex-none text-right text-[10px] text-gray-400" x-text="upload.progress + '%'"></span>
                <span x-show="upload.status === 'done'" class="flex-none text-[10px] font-medium text-green-600">Uploaded</span>
                <span x-show="upload.status === 'error'" class="flex-none text-[10px] font-medium text-red-600" x-text="upload.error || 'Failed'"></span>

                <button type="button" class="flex-none text-gray-400 hover:text-red-600" @click="removeUpload(upload)" title="Remove">&times;</button>

                <template x-if="upload.status === 'done'">
                    <input type="hidden" name="media_ids[]" :value="upload.mediaId">
                </template>
            </li>
        </template>
    </ul>

    <div class="grid grid-cols-2 gap-2 p-3 sm:grid-cols-4">
        <div class="flex flex-col items-center gap-2 rounded-md border border-dashed border-gray-300 px-2 py-3 text-center dark:border-gray-600">
            <x-icons.play-circle class="h-5 w-5 text-gray-400" />
            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Video</span>
            <div class="flex w-full flex-col gap-1">
                <label class="flex cursor-pointer items-center justify-center rounded-full border border-blue-600 bg-blue-600 px-2 py-1 text-[10px] font-medium text-white transition hover:bg-blue-700">
                    Upload
                    <input type="file" accept="video/mp4,video/webm" multiple class="hidden" @change="handleFiles($event, 'video')">
                </label>
                <label class="flex cursor-pointer items-center justify-center rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black transition hover:bg-gray-100">
                    Upload to our server
                    <input type="file" accept="video/mp4,video/webm" multiple class="hidden" @change="handleFiles($event, 'video', 'own_server')">
                </label>
                <button type="button" @click="openMuxPicker('video')" class="rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black transition hover:bg-gray-100">
                    Use existing
                </button>
            </div>
        </div>
        <div class="flex flex-col items-center gap-2 rounded-md border border-dashed border-gray-300 px-2 py-3 text-center dark:border-gray-600">
            <x-icons.music-note class="h-5 w-5 text-gray-400" />
            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Audio</span>
            <div class="flex w-full flex-col gap-1">
                <label class="flex cursor-pointer items-center justify-center rounded-full border border-blue-600 bg-blue-600 px-2 py-1 text-[10px] font-medium text-white transition hover:bg-blue-700">
                    Upload
                    <input type="file" accept="audio/mpeg,audio/wav,audio/mp4" multiple class="hidden" @change="handleFiles($event, 'audio')">
                </label>
                <label class="flex cursor-pointer items-center justify-center rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black transition hover:bg-gray-100">
                    Upload to our server
                    <input type="file" accept="audio/mpeg,audio/wav,audio/mp4" multiple class="hidden" @change="handleFiles($event, 'audio', 'own_server')">
                </label>
                <button type="button" @click="openMuxPicker('audio')" class="rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black transition hover:bg-gray-100">
                    Use existing
                </button>
            </div>
        </div>
        <div class="flex flex-col items-center gap-2 rounded-md border border-dashed border-gray-300 px-2 py-3 text-center dark:border-gray-600">
            <x-icons.photo class="h-5 w-5 text-gray-400" />
            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Image</span>
            <div class="flex w-full flex-col gap-1">
                <label class="flex cursor-pointer items-center justify-center rounded-full border border-blue-600 bg-blue-600 px-2 py-1 text-[10px] font-medium text-white transition hover:bg-blue-700">
                    Upload
                    <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="handleFiles($event, 'image')">
                </label>
                <button type="button" @click="openLibraryPicker('image')" class="rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black transition hover:bg-gray-100">
                    Use existing
                </button>
            </div>
        </div>
        <div class="flex flex-col items-center gap-2 rounded-md border border-dashed border-gray-300 px-2 py-3 text-center dark:border-gray-600">
            <x-icons.book-open class="h-5 w-5 text-gray-400" />
            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Document</span>
            <div class="flex w-full flex-col gap-1">
                <label class="flex cursor-pointer items-center justify-center rounded-full border border-blue-600 bg-blue-600 px-2 py-1 text-[10px] font-medium text-white transition hover:bg-blue-700">
                    Upload
                    <input type="file" accept="application/pdf,application/epub+zip" multiple class="hidden" @change="handleFiles($event, 'document')">
                </label>
                <button type="button" @click="openLibraryPicker('document')" class="rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black transition hover:bg-gray-100">
                    Use existing
                </button>
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="lightboxUrl" x-cloak x-data="{ zoomed: false }" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/80" @click="closeLightbox(); zoomed = false"></div>

            <div class="relative max-h-[85vh] w-full max-w-3xl overflow-auto rounded-lg" :class="zoomed ? 'cursor-zoom-out' : ''">
                <img :src="lightboxUrl" @click="zoomed = !zoomed" class="mx-auto rounded-lg shadow-xl transition-transform duration-200" :class="zoomed ? 'max-w-none scale-150 cursor-zoom-out' : 'max-h-[85vh] max-w-full cursor-zoom-in'">
            </div>

            <div class="absolute right-4 top-4 flex gap-2">
                <button type="button" @click="zoomed = !zoomed" :title="zoomed ? 'Zoom out' : 'Zoom in'" class="flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white shadow-lg hover:bg-black/80">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m21 21-4.35-4.35" />
                        <path x-show="!zoomed" d="M11 8v6M8 11h6" />
                        <path x-show="zoomed" d="M8 11h6" />
                    </svg>
                </button>
                <button type="button" @click="closeLightbox(); zoomed = false" title="Close" class="flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-xl font-light text-white shadow-lg hover:bg-black/80">&times;</button>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="muxPicker.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/60" @click="closeMuxPicker()"></div>

            <div class="relative max-h-[80vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-black/10 bg-white p-5 shadow-xl dark:border-white/10 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white" x-text="'Select an existing ' + (muxPicker.type === 'audio' ? 'audio file' : 'video') + ' from Mux'"></h3>
                    <button type="button" @click="closeMuxPicker()" class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Close</button>
                </div>

                <div class="mt-4" x-show="muxPicker.loading">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Loading&hellip;</p>
                </div>

                <div class="mt-4" x-show="muxPicker.error" x-cloak>
                    <p class="text-sm text-red-600" x-text="muxPicker.error"></p>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3" x-show="!muxPicker.loading && !muxPicker.error">
                    <template x-if="!muxPicker.loading && !muxPicker.error && muxPicker.items.length === 0">
                        <p class="col-span-full text-sm text-gray-400">Nothing found in your Mux library yet.</p>
                    </template>
                    <template x-for="item in muxPicker.items" :key="item.asset_id">
                        <button type="button" @click="selectMuxAsset(item)" class="group overflow-hidden rounded-lg border border-gray-200 text-left hover:border-indigo-400 dark:border-gray-600">
                            <template x-if="muxPicker.type === 'video'">
                                <div class="aspect-video w-full bg-gray-100 dark:bg-gray-900">
                                    <img :src="item.thumbnail_url" class="h-full w-full object-cover" loading="lazy">
                                </div>
                            </template>
                            <template x-if="muxPicker.type === 'audio'">
                                <div class="flex aspect-video w-full items-center justify-center bg-gray-100 text-indigo-400 dark:bg-gray-900">
                                    <x-icons.music-note class="h-6 w-6" />
                                </div>
                            </template>
                            <p class="truncate px-2 py-1.5 text-[11px] text-gray-600 group-hover:text-indigo-600 dark:text-gray-300" x-text="item.title || item.asset_id.slice(0, 12) + '…'"></p>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="libraryPicker.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div class="fixed inset-0 bg-black/60" @click="closeLibraryPicker()"></div>

            <div class="relative max-h-[80vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-black/10 bg-white p-5 shadow-xl dark:border-white/10 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white" x-text="'Select an existing ' + libraryPicker.type"></h3>
                    <button type="button" @click="closeLibraryPicker()" class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Close</button>
                </div>

                <div class="mt-4" x-show="libraryPicker.loading">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Loading&hellip;</p>
                </div>

                <div class="mt-4" x-show="libraryPicker.error" x-cloak>
                    <p class="text-sm text-red-600" x-text="libraryPicker.error"></p>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3" x-show="!libraryPicker.loading && !libraryPicker.error">
                    <template x-if="!libraryPicker.loading && !libraryPicker.error && libraryPicker.items.length === 0">
                        <p class="col-span-full text-sm text-gray-400">Nothing uploaded yet.</p>
                    </template>
                    <template x-for="item in libraryPicker.items" :key="item.id">
                        <button type="button" @click="selectLibraryAsset(item)" class="group overflow-hidden rounded-lg border border-gray-200 text-left hover:border-indigo-400 dark:border-gray-600">
                            <template x-if="libraryPicker.type === 'image'">
                                <div class="aspect-video w-full bg-gray-100 dark:bg-gray-900">
                                    <img :src="item.resolved_url" class="h-full w-full object-cover" loading="lazy">
                                </div>
                            </template>
                            <template x-if="libraryPicker.type === 'document'">
                                <div class="flex aspect-video w-full items-center justify-center bg-gray-100 text-indigo-400 dark:bg-gray-900">
                                    <x-icons.book-open class="h-6 w-6" />
                                </div>
                            </template>
                            <p class="truncate px-2 py-1.5 text-[11px] text-gray-600 group-hover:text-indigo-600 dark:text-gray-300" x-text="item.title || ('File #' + item.id)"></p>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>
