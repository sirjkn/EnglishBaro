@props(['media' => null, 'name' => 'media_id'])

<div
    x-data="{
        ...lessonMediaUploader(),
        selectedMediaId: {{ $media?->id ?? 'null' }},
        selectedMediaType: {{ $media ? \Illuminate\Support\Js::from($media->type) : 'null' }},
        selectedMediaLabel: {{ $media ? \Illuminate\Support\Js::from($media->title ?? $media->original_filename) : 'null' }},
    }"
    x-init="$watch('uploads', (value) => {
        const latest = [...value].reverse().find((u) => u.status === 'done');
        if (latest) {
            selectedMediaId = latest.mediaId;
            selectedMediaType = latest.type;
            selectedMediaLabel = latest.name;
        }
    })"
>
    <input type="hidden" name="{{ $name }}" :value="selectedMediaId ?? ''">

    <div x-show="selectedMediaId" x-cloak class="mb-2 flex items-center gap-2 rounded-md bg-gray-50 px-2 py-1.5 text-xs dark:bg-gray-900">
        <span class="flex-none capitalize text-indigo-600 dark:text-indigo-400" x-text="selectedMediaType"></span>
        <span class="min-w-0 flex-1 truncate text-gray-600 dark:text-gray-300" x-text="selectedMediaLabel"></span>
        <button type="button" @click="selectedMediaId = null; selectedMediaType = null; selectedMediaLabel = null" class="flex-none text-gray-400 hover:text-red-600" title="Remove">&times;</button>
    </div>

    <div class="grid grid-cols-3 gap-2">
        <div class="flex flex-col items-center gap-1.5 rounded-md border border-dashed border-gray-300 px-2 py-2 text-center dark:border-gray-600">
            <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">Video</span>
            <label class="flex w-full cursor-pointer items-center justify-center rounded-full border border-blue-600 bg-blue-600 px-2 py-1 text-[10px] font-medium text-white hover:bg-blue-700">
                Upload
                <input type="file" accept="video/mp4,video/webm" class="hidden" @change="handleFiles($event, 'video', 'own_server')">
            </label>
            <label class="flex w-full cursor-pointer items-center justify-center rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black hover:bg-gray-100">
                3rd party server
                <input type="file" accept="video/mp4,video/webm" class="hidden" @change="handleFiles($event, 'video')">
            </label>
            <button type="button" @click="openMuxPicker('video')" class="w-full rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black hover:bg-gray-100">
                Use existing
            </button>
            <div x-data="{ url: '' }" class="w-full">
                <input type="text" x-model="url" placeholder="Video link" class="w-full rounded-full border-gray-300 px-2 py-1 text-[10px] dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                <button type="button" @click="attachExternalVideo(url); url = ''" class="mt-1 w-full rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black hover:bg-gray-100">Add link</button>
            </div>
        </div>
        <div class="flex flex-col items-center gap-1.5 rounded-md border border-dashed border-gray-300 px-2 py-2 text-center dark:border-gray-600">
            <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">Audio</span>
            <label class="flex w-full cursor-pointer items-center justify-center rounded-full border border-blue-600 bg-blue-600 px-2 py-1 text-[10px] font-medium text-white hover:bg-blue-700">
                Upload
                <input type="file" accept="audio/mpeg,audio/wav,audio/mp4" class="hidden" @change="handleFiles($event, 'audio', 'own_server')">
            </label>
            <label class="flex w-full cursor-pointer items-center justify-center rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black hover:bg-gray-100">
                3rd party server
                <input type="file" accept="audio/mpeg,audio/wav,audio/mp4" class="hidden" @change="handleFiles($event, 'audio')">
            </label>
            <button type="button" @click="openMuxPicker('audio')" class="w-full rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black hover:bg-gray-100">
                Use existing
            </button>
        </div>
        <div class="flex flex-col items-center gap-1.5 rounded-md border border-dashed border-gray-300 px-2 py-2 text-center dark:border-gray-600">
            <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">Image</span>
            <label class="flex w-full cursor-pointer items-center justify-center rounded-full border border-blue-600 bg-blue-600 px-2 py-1 text-[10px] font-medium text-white hover:bg-blue-700">
                Upload
                <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="handleFiles($event, 'image')">
            </label>
            <button type="button" @click="openLibraryPicker('image')" class="w-full rounded-full border border-gray-300 bg-white px-2 py-1 text-[10px] font-medium text-black hover:bg-gray-100">
                Use existing
            </button>
        </div>
    </div>

    <template x-if="uploads.some((u) => u.status === 'uploading' || u.status === 'error')">
        <ul class="mt-2 space-y-1">
            <template x-for="upload in uploads.filter((u) => u.status === 'uploading' || u.status === 'error')" :key="upload.key">
                <li class="rounded-md bg-gray-50 px-2 py-1 text-[11px] dark:bg-gray-900">
                    <span x-text="upload.name"></span>
                    <span x-show="upload.status === 'error'" class="text-red-600" x-text="' — ' + (upload.error || 'Failed')"></span>

                    <template x-if="upload.status === 'uploading'">
                        <div class="mt-1 flex items-center gap-2">
                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-full bg-indigo-500 transition-all" :style="'width: ' + upload.progress + '%'"></div>
                            </div>
                            <span class="w-9 flex-none text-right text-gray-400" x-text="upload.progress + '%'"></span>
                        </div>
                    </template>
                </li>
            </template>
        </ul>
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
                            <template x-if="libraryPicker.type !== 'image'">
                                <div class="flex aspect-video w-full items-center justify-center bg-gray-100 text-indigo-400 dark:bg-gray-900">
                                    <x-icons.music-note class="h-6 w-6" />
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
