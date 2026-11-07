<x-layouts.admin :title="'Settings'">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>

    @php
        $activeTab = old('_tab', request()->query('tab', 'company'));
    @endphp

    <div x-data="{ tab: '{{ $activeTab }}' }" class="mt-6">
        <div class="flex flex-wrap gap-1 border-b border-gray-200 dark:border-gray-700">
            <button type="button" @click="tab = 'company'"
                    :class="tab === 'company' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="border-b-2 px-4 py-2.5 text-sm font-medium">
                Company Settings
            </button>
            <button type="button" @click="tab = 'payment'"
                    :class="tab === 'payment' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="border-b-2 px-4 py-2.5 text-sm font-medium">
                Payment Gateways
            </button>
            <button type="button" @click="tab = 'system'"
                    :class="tab === 'system' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="border-b-2 px-4 py-2.5 text-sm font-medium">
                System Settings
            </button>
            <button type="button" @click="tab = 'backup'"
                    :class="tab === 'backup' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="border-b-2 px-4 py-2.5 text-sm font-medium">
                Backup &amp; Restore
            </button>
            <a href="{{ route('admin.users.index') }}"
               class="border-b-2 border-transparent px-4 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                Users
            </a>
        </div>

    <div x-show="tab === 'company'" x-cloak class="mt-6 overflow-hidden rounded-xl border border-black/10 bg-white dark:border-white/10 dark:bg-gray-800">
        <div class="bg-indigo-600 px-6 py-3 dark:bg-indigo-800">
            <h2 class="text-sm font-bold text-white">Company Settings</h2>
        </div>
        <form method="POST" action="{{ route('admin.settings.company') }}" class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
            <input type="hidden" name="_tab" value="company">
            @csrf
            @method('PUT')

            @foreach ([
                'company_name' => 'Company Name',
                'address' => 'Address',
                'phone' => 'Phone',
                'email' => 'Email',
                'website' => 'Website',
                'support_email' => 'Support Email',
                'footer_text' => 'Footer Text',
                'copyright' => 'Copyright',
            ] as $key => $label)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                    <input type="text" name="{{ $key }}" value="{{ old($key, $company[$key] ?? '') }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                </div>
            @endforeach

            <div class="sm:col-span-2">
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Company Settings</button>
            </div>
        </form>

    </div>

    <div x-show="tab === 'payment'" x-cloak class="mt-6 overflow-hidden rounded-xl border border-black/10 bg-white dark:border-white/10 dark:bg-gray-800">
        <div class="bg-indigo-600 px-6 py-3 dark:bg-indigo-800">
            <h2 class="text-sm font-bold text-white">Payment Gateways</h2>
        </div>
        <div class="p-6">
        <p class="text-xs text-gray-500 dark:text-gray-400">Credentials are encrypted at rest and never exposed to the frontend.</p>

        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ($gateways as $gateway)
                @php $setting = $paymentSettings[$gateway] ?? null; @endphp
                <form method="POST" action="{{ route('admin.settings.payment') }}" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_tab" value="payment">
                    <input type="hidden" name="gateway" value="{{ $gateway }}">

                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-gray-900 dark:text-white">{{ ucfirst($gateway) }}</h3>
                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="is_enabled" value="1" @checked($setting?->is_enabled) class="rounded border-gray-300 text-indigo-600">
                            Enabled
                        </label>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="is_sandbox" value="1" @checked($setting?->is_sandbox ?? true) class="rounded border-gray-300 text-indigo-600">
                            Sandbox Mode
                        </label>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <input type="text" name="config[public_key]" placeholder="Public / Consumer Key" value="{{ $setting?->config['public_key'] ?? '' }}" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        <input type="password" name="config[secret_key]" placeholder="Secret Key" value="{{ $setting?->config['secret_key'] ?? '' }}" class="rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>

                    <button type="submit" class="mt-3 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Save {{ ucfirst($gateway) }} Settings</button>
                </form>
            @endforeach
        </div>
        </div>
    </div>

    <div x-show="tab === 'system'" x-cloak class="mt-6 overflow-hidden rounded-xl border border-black/10 bg-white dark:border-white/10 dark:bg-gray-800">
        <div class="bg-indigo-600 px-6 py-3 dark:bg-indigo-800">
            <h2 class="text-sm font-bold text-white">System Settings</h2>
        </div>
        <div class="p-6">

        <div x-data="{ dark: false }" x-init="try { dark = localStorage.getItem('theme') === 'dark' } catch (e) {}" class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Dark Mode</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Off by default. This only affects your own browser.</p>
            </div>
            <button
                type="button"
                @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); try { localStorage.setItem('theme', dark ? 'dark' : 'light') } catch (e) {}"
                :class="dark ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'"
                class="relative inline-flex h-6 w-11 flex-none items-center rounded-full transition-colors"
                role="switch"
                :aria-checked="dark.toString()"
            >
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="dark ? 'translate-x-6' : 'translate-x-1'"></span>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.settings.system') }}" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            @method('PUT')
            <input type="hidden" name="_tab" value="system">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Subscription Days</label>
                <input type="number" name="default_subscription_days" min="1" value="{{ old('default_subscription_days', $system['default_subscription_days'] ?? 120) }}" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>

            <div class="sm:col-span-2 flex items-center justify-between rounded-lg border-2 border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-900/20">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Under Construction Mode</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        When on, every visitor except a logged-in admin sees an "under construction" page instead of the real site. Admins can still sign in and browse normally.
                    </p>
                </div>
                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                    <input type="checkbox" name="site_maintenance_mode" value="1" {{ old('site_maintenance_mode', $system['site_maintenance_mode'] ?? '0') == '1' ? 'checked' : '' }} class="peer sr-only">
                    <div class="h-6 w-11 rounded-full bg-gray-300 peer-checked:bg-amber-500 dark:bg-gray-600"></div>
                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></div>
                </label>
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save System Settings</button>
            </div>
        </form>
        </div>
    </div>

    <div x-show="tab === 'backup'" x-cloak class="mt-6 space-y-6">
        <div class="rounded-xl border border-black/10 bg-white p-6 dark:border-white/10 dark:bg-gray-800">
            <h2 class="font-semibold text-gray-900 dark:text-white">Backup</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Download a full snapshot of the database, plus every locally-stored image and document, as a single .zip file. Videos and audio already live on Mux and aren't duplicated into the backup.
            </p>
            <a href="{{ route('admin.settings.backup.download') }}" class="mt-4 inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                <x-icons.download class="h-4 w-4" />
                Download Backup
            </a>
        </div>

        <div class="rounded-xl border-2 border-red-200 bg-white p-6 dark:border-red-900 dark:bg-gray-800"
             x-data="{
                step: 0, typed: '', file: null,
                running: false, error: null,
                totalTables: 0, doneTables: 0, mediaRestored: null,
                log: [],
                previewLoading: false, previewError: null, preview: null,
                get percent() {
                    if (this.totalTables === 0) return this.running ? 5 : 0;
                    return Math.min(100, Math.round((this.doneTables / this.totalTables) * 100));
                },
                async onFileChosen(selected) {
                    this.file = selected;
                    this.preview = null;
                    this.previewError = null;
                    if (!selected) return;

                    this.previewLoading = true;
                    const formData = new FormData();
                    formData.append('backup_file', selected);
                    formData.append('_token', document.querySelector('meta[name=csrf-token]').content);

                    try {
                        const res = await fetch('{{ route('admin.settings.backup.restore.preview') }}', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            body: formData,
                        });
                        const json = await res.json();
                        if (!res.ok) {
                            this.previewError = json.error || 'Could not read that file.';
                        } else {
                            this.preview = json;
                        }
                    } catch (e) {
                        this.previewError = 'Could not read that file: ' + e.message;
                    } finally {
                        this.previewLoading = false;
                    }
                },
                async runRestore() {
                    this.running = true;
                    this.error = null;
                    this.log = [];
                    this.doneTables = 0;
                    this.totalTables = this.preview ? this.preview.total_tables : 0;
                    this.mediaRestored = null;

                    // The server does the real restore in one request (shared
                    // hosting often buffers streamed output until the whole
                    // response finishes, so true live progress isn't
                    // reliable) — this walks the table list we already have
                    // from the preview as a progress indicator while that
                    // request is in flight, then reconciles with the real
                    // per-table result once it comes back.
                    const previewTables = this.preview ? this.preview.tables : [];
                    let cancelled = false;
                    const tick = (i) => {
                        if (cancelled || i >= previewTables.length) return;
                        this.doneTables = i + 1;
                        this.log.unshift({ table: previewTables[i].table, rows: previewTables[i].rows, dropped: [] });
                        setTimeout(() => tick(i + 1), Math.max(40, Math.min(300, 6000 / Math.max(previewTables.length, 1))));
                    };
                    tick(0);

                    const formData = new FormData();
                    formData.append('backup_file', this.file);
                    formData.append('confirm', this.typed);
                    formData.append('_token', document.querySelector('meta[name=csrf-token]').content);

                    try {
                        const res = await fetch('{{ route('admin.settings.backup.restore') }}', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            body: formData,
                        });
                        const json = await res.json();
                        cancelled = true;

                        if (!res.ok) {
                            this.error = json.error || 'Restore failed.';
                            this.running = false;
                            return;
                        }

                        this.totalTables = json.tables.length;
                        this.doneTables = json.tables.length;
                        this.log = json.tables.slice().reverse().map(t => ({ table: t.table, rows: t.rows, dropped: t.dropped_columns || [] }));
                        this.mediaRestored = json.media_restored;
                        this.running = false;
                    } catch (e) {
                        cancelled = true;
                        this.error = 'Restore request failed: ' + e.message;
                        this.running = false;
                    }
                },
             }">
            <h2 class="font-semibold text-red-700 dark:text-red-300">Restore</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Uploading a backup file <strong>replaces every record currently in the database</strong> with the contents of that file. This cannot be undone. Make sure you have a current backup before restoring an older one.
            </p>

            <div class="mt-4">
                <input type="file" accept=".zip,.json,application/zip,application/json" @change="onFileChosen($event.target.files[0] || null)" class="block w-full text-sm text-gray-600 dark:text-gray-300">
            </div>

            <template x-if="previewLoading">
                <p class="mt-3 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span class="animate-spin">&#8635;</span> Reading backup file&hellip;
                </p>
            </template>

            <template x-if="previewError">
                <p class="mt-3 text-sm text-red-600 dark:text-red-400" x-text="previewError"></p>
            </template>

            <template x-if="preview && !previewLoading">
                <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        Backup from <span x-text="preview.created_at ? new Date(preview.created_at).toLocaleString() : 'unknown date'"></span>
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        <span x-text="preview.total_tables"></span> tables &middot; <span x-text="preview.total_rows.toLocaleString()"></span> rows total &middot; <span x-text="preview.media_files"></span> media file(s)
                    </p>
                    <div class="mt-2 max-h-40 space-y-0.5 overflow-y-auto text-xs">
                        <template x-for="t in preview.tables" :key="t.table">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-gray-700 dark:text-gray-300" x-text="t.table"></span>
                                <span class="text-gray-500 dark:text-gray-400" x-text="t.rows + ' row(s)'"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <button type="button" @click="step = 1" :disabled="!file || previewLoading || previewError" class="mt-4 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-50">
                Restore From File
            </button>

            <template x-teleport="body">
                <div x-show="step === 1" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                    <div class="fixed inset-0 bg-black/50" @click="step = 0"></div>
                    <div class="relative w-full max-w-sm rounded-xl border-2 border-red-300 bg-white p-6 shadow-xl dark:border-red-900 dark:bg-gray-800">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-xl font-bold text-red-600 dark:bg-red-900/40 dark:text-red-300">!</span>
                            <h2 class="text-base font-semibold text-red-700 dark:text-red-300">Replace all data?</h2>
                        </div>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                            This will permanently delete every student, track, payment, and support record currently in the database and replace them with the uploaded file's contents. This cannot be undone.
                        </p>
                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button" @click="step = 0" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                            <button type="button" @click="step = 2" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Continue</button>
                        </div>
                    </div>
                </div>

                <div x-show="step === 2" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                    <div class="fixed inset-0 bg-black/50" @click="if (!running) { step = 0; typed = ''; }"></div>
                    <div class="relative w-full max-w-lg rounded-xl border-2 border-red-300 bg-white p-6 shadow-xl dark:border-red-900 dark:bg-gray-800">
                        <template x-if="!running && !mediaRestored && !error">
                            <div>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-xl font-bold text-red-600 dark:bg-red-900/40 dark:text-red-300">!</span>
                                    <h2 class="text-base font-semibold text-red-700 dark:text-red-300">Final confirmation</h2>
                                </div>
                                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                                    Type <span class="font-mono font-bold text-red-600">RESTORE</span> below to confirm.
                                </p>
                                <input type="text" x-model="typed" autocomplete="off" class="mt-3 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white" placeholder="Type RESTORE">
                                <div class="mt-5 flex justify-end gap-2">
                                    <button type="button" @click="step = 0; typed = ''" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Cancel</button>
                                    <button type="button" :disabled="typed !== 'RESTORE'" @click="runRestore()"
                                            class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-50">
                                        Permanently Restore
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="running || mediaRestored || error">
                            <div>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xl font-bold"
                                          :class="error ? 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300' : (running ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-300')">
                                        <span x-show="error">!</span>
                                        <span x-show="!error && running" class="animate-spin">&#8635;</span>
                                        <span x-show="!error && !running">&#10003;</span>
                                    </span>
                                    <h2 class="text-base font-semibold"
                                        :class="error ? 'text-red-700 dark:text-red-300' : 'text-gray-900 dark:text-white'"
                                        x-text="error ? 'Restore failed' : (running ? 'Restoring…' : 'Restore complete')"></h2>
                                </div>

                                <template x-if="error">
                                    <p class="mt-3 text-sm text-red-600 dark:text-red-400" x-text="error"></p>
                                </template>

                                <template x-if="!error">
                                    <div class="mt-4">
                                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                            <div class="h-2.5 rounded-full bg-indigo-600 transition-all duration-300" :style="`width: ${percent}%`"></div>
                                        </div>
                                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                            <span x-text="doneTables"></span> / <span x-text="totalTables"></span> tables &mdash; <span x-text="percent"></span>%
                                            <template x-if="mediaRestored !== null"> &middot; <span x-text="mediaRestored"></span> media file(s) restored</template>
                                        </p>
                                    </div>
                                </template>

                                <div class="mt-4 max-h-56 space-y-1 overflow-y-auto rounded-md border border-gray-200 bg-gray-50 p-2 text-xs dark:border-gray-700 dark:bg-gray-900">
                                    <template x-for="entry in log" :key="entry.table + (entry.rows ?? '')">
                                        <div class="flex items-center justify-between gap-2 py-0.5">
                                            <span class="font-mono text-gray-700 dark:text-gray-300" x-text="entry.table"></span>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                <template x-if="!entry.media"><span><span x-text="entry.rows"></span> row(s)</span></template>
                                                <template x-if="entry.dropped.length"><span class="ml-2 text-amber-600 dark:text-amber-400">dropped: <span x-text="entry.dropped.join(', ')"></span></span></template>
                                            </span>
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-5 flex justify-end gap-2">
                                    <button type="button" :disabled="running" @click="step = 0; typed = ''; file = null; error = null; mediaRestored = null; log = [];"
                                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
    </div>
</x-layouts.admin>
