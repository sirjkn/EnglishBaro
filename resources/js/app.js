import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('lessonMediaUploader', (lessonId = null) => ({
    uploads: [],
    nextKey: 1,
    lightboxUrl: null,
    lessonId,
    muxPicker: {
        open: false,
        loading: false,
        error: null,
        items: [],
        type: 'video',
    },
    libraryPicker: {
        open: false,
        loading: false,
        error: null,
        items: [],
        type: 'image',
    },

    get isUploading() {
        return this.uploads.some((u) => u.status === 'uploading');
    },

    // Uploading used to just show "Uploaded" and leave attaching it to the
    // lesson to a separate, easy-to-miss "Save" click below — admins kept
    // uploading videos that never actually got attached. Once editing an
    // existing lesson, finishing an upload now submits the form itself so
    // there's nothing left to forget.
    autoSaveIfReady() {
        if (! this.lessonId || this.isUploading) return;

        this.$nextTick(() => {
            if (this.$el.requestSubmit) {
                this.$el.requestSubmit();
            } else {
                this.$el.submit();
            }
        });
    },

    openLightbox(url) {
        this.lightboxUrl = url;
    },

    closeLightbox() {
        this.lightboxUrl = null;
    },

    openMuxPicker(type = 'video') {
        this.muxPicker.open = true;
        this.muxPicker.type = type;
        this.loadMuxAssets();
    },

    closeMuxPicker() {
        this.muxPicker.open = false;
    },

    loadMuxAssets() {
        if (! window.adminMuxAssetsUrl) return;

        this.muxPicker.loading = true;
        this.muxPicker.error = null;

        const url = `${window.adminMuxAssetsUrl}?type=${encodeURIComponent(this.muxPicker.type)}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => {
                if (! r.ok) throw new Error('Could not load files from Mux.');
                return r.json();
            })
            .then((data) => {
                this.muxPicker.items = data.data || [];
            })
            .catch((e) => {
                this.muxPicker.error = e.message || 'Could not load files from Mux.';
            })
            .finally(() => {
                this.muxPicker.loading = false;
            });
    },

    selectMuxAsset(item) {
        const type = this.muxPicker.type;

        const upload = Alpine.reactive({
            key: this.nextKey++,
            name: item.title || `Existing Mux ${type} (${item.asset_id.slice(0, 8)})`,
            type,
            previewUrl: type === 'video' ? item.thumbnail_url || null : null,
            progress: 100,
            status: 'uploading',
            mediaId: null,
            error: null,
            xhr: null,
        });

        this.uploads.push(upload);
        this.closeMuxPicker();

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (! csrfMeta || ! window.adminMuxFromAssetUrl) {
            upload.status = 'error';
            upload.error = 'Not configured';
            return;
        }

        fetch(window.adminMuxFromAssetUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ asset_id: item.asset_id, type }),
        })
            .then((r) => {
                if (! r.ok) throw new Error('Could not attach that file.');
                return r.json();
            })
            .then((data) => {
                upload.mediaId = data.id;
                upload.status = 'done';
                this.autoSaveIfReady();
            })
            .catch((e) => {
                upload.status = 'error';
                upload.error = e.message || 'Could not attach that file.';
            });
    },

    openLibraryPicker(type = 'image') {
        this.libraryPicker.open = true;
        this.libraryPicker.type = type;
        this.loadLibraryAssets();
    },

    closeLibraryPicker() {
        this.libraryPicker.open = false;
    },

    loadLibraryAssets() {
        if (! window.adminMediaLibraryUrl) return;

        this.libraryPicker.loading = true;
        this.libraryPicker.error = null;

        const url = `${window.adminMediaLibraryUrl}?type=${encodeURIComponent(this.libraryPicker.type)}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => {
                if (! r.ok) throw new Error('Could not load your media library.');
                return r.json();
            })
            .then((data) => {
                this.libraryPicker.items = data.data || [];
            })
            .catch((e) => {
                this.libraryPicker.error = e.message || 'Could not load your media library.';
            })
            .finally(() => {
                this.libraryPicker.loading = false;
            });
    },

    selectLibraryAsset(item) {
        const type = this.libraryPicker.type;

        this.uploads.push(Alpine.reactive({
            key: this.nextKey++,
            name: item.title || `Existing ${type} #${item.id}`,
            type,
            previewUrl: type === 'image' ? item.resolved_url : null,
            progress: 100,
            status: 'done',
            mediaId: item.id,
            error: null,
            xhr: null,
        }));

        this.closeLibraryPicker();
        this.autoSaveIfReady();
    },

    handleFiles(event, type) {
        const input = event.target;
        const files = Array.from(input.files || []);

        files.forEach((file) => this.uploadFile(file, type));

        input.value = '';
    },

    uploadFile(file, type) {
        // Must be wrapped in Alpine.reactive() *before* being captured by the
        // xhr closures below — mutating a plain object after push() bypasses
        // the array proxy's reactivity and silently fails to re-render.
        const upload = Alpine.reactive({
            key: this.nextKey++,
            name: file.name,
            type,
            previewUrl: type === 'image' ? URL.createObjectURL(file) : null,
            progress: 0,
            status: 'uploading',
            mediaId: null,
            error: null,
            xhr: null,
        });

        this.uploads.push(upload);

        try {
            const uploadUrl = window.adminMediaUploadUrl;
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');

            if (! uploadUrl || ! csrfMeta) {
                throw new Error('Upload is not configured on this page.');
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', type);

            const xhr = new XMLHttpRequest();
            upload.xhr = xhr;

            xhr.open('POST', uploadUrl, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfMeta.content);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.timeout = 20 * 60 * 1000;

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    upload.progress = Math.round((e.loaded / e.total) * 100);
                } else {
                    upload.progress = 50;
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        upload.mediaId = data.id;
                        upload.progress = 100;
                        upload.status = 'done';
                        this.autoSaveIfReady();
                    } catch (e) {
                        upload.status = 'error';
                        upload.error = 'Unexpected server response';
                    }
                } else {
                    upload.status = 'error';
                    try {
                        const data = JSON.parse(xhr.responseText);
                        upload.error = data.message || `Upload failed (${xhr.status})`;
                    } catch (e) {
                        upload.error = `Upload failed (${xhr.status})`;
                    }
                }
            });

            xhr.addEventListener('error', () => {
                upload.status = 'error';
                upload.error = 'Network error during upload';
            });

            xhr.addEventListener('timeout', () => {
                upload.status = 'error';
                upload.error = 'Upload timed out';
            });

            xhr.addEventListener('abort', () => {
                upload.status = 'error';
                upload.error = 'Cancelled';
            });

            xhr.send(formData);
        } catch (e) {
            upload.status = 'error';
            upload.error = e.message || 'Could not start upload';
            console.error('Media upload failed to start', e);
        }
    },

    removeUpload(upload) {
        if (upload.status === 'uploading' && upload.xhr) {
            upload.xhr.abort();
        } else if (upload.status === 'done' && upload.mediaId) {
            fetch(`/admin/media-uploads/${upload.mediaId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        }

        if (upload.previewUrl) {
            URL.revokeObjectURL(upload.previewUrl);
        }

        this.uploads = this.uploads.filter((u) => u.key !== upload.key);
    },

    iconFor(type) {
        return { video: '\u{1F3AC}', audio: '\u{1F3B5}', image: '\u{1F5BC}', document: '\u{1F4C4}' }[type] || '';
    },
}));

Alpine.data('lessonVideoTracker', (resumeSeconds, progressUrl, completion = null) => ({
    hasVideo: false,
    watchedPercent: 0,
    lastSent: 0,

    // Optional: lets a lesson mark itself complete (and, once the course
    // architecture inlines a lesson's video+questions into the level list
    // instead of a separate page, auto-advance to the next one) without
    // leaving this component's scope — `completion` is null on the
    // standalone lesson page, which keeps its own classic Mark Completed
    // form untouched.
    completed: completion?.completed ?? false,
    marking: false,
    completeError: null,

    get videoSatisfied() {
        return ! this.hasVideo || this.watchedPercent >= 0.98;
    },

    markCompleted() {
        if (! completion || this.completed || this.marking || ! this.videoSatisfied) return;

        this.marking = true;
        this.completeError = null;

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');

        fetch(completion.completeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({}),
        })
            .then((r) => {
                if (! r.ok) throw new Error('Could not mark this lesson complete.');
                return r.json();
            })
            .then(() => {
                this.completed = true;
                if (! completion.hasAssessment) {
                    this.$dispatch('lesson-advance', completion.advance);
                }
            })
            .catch((e) => {
                this.completeError = e.message || 'Could not mark this lesson complete.';
            })
            .finally(() => {
                this.marking = false;
            });
    },

    initPlayer(el) {
        this.hasVideo = true;

        const resume = () => {
            const duration = el.duration || 0;
            if (resumeSeconds > 2 && duration && resumeSeconds < duration - 3) {
                try {
                    el.currentTime = resumeSeconds;
                } catch (e) {
                    // Some players aren't seekable until further into load; ignore.
                }
            }
        };
        el.addEventListener('loadedmetadata', resume, { once: true });
        el.addEventListener('loadeddata', resume, { once: true });

        el.addEventListener('timeupdate', () => {
            const duration = el.duration || 0;
            if (duration > 0) {
                const pct = el.currentTime / duration;
                if (pct > this.watchedPercent) {
                    this.watchedPercent = pct;
                }
            }

            const now = Date.now();
            if (now - this.lastSent > 5000) {
                this.lastSent = now;
                this.sendProgress(Math.floor(el.currentTime));
            }
        });

        el.addEventListener('ended', () => {
            this.watchedPercent = 1;
            this.sendProgress(Math.floor(el.duration || el.currentTime));
        });

        el.addEventListener('pause', () => {
            this.sendProgress(Math.floor(el.currentTime));
        });
    },

    sendProgress(seconds) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (! csrfMeta) return;

        fetch(progressUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfMeta.content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ seconds }),
        }).catch(() => {});
    },
}));

Alpine.data('lessonActivity', (config) => ({
    answers: config.initialAnswers || {},
    feedback: config.initialFeedback || {},
    checked: config.alreadyChecked,
    score: config.initialScore,
    passed: config.initialPassed,
    attemptsUsed: config.attemptsUsed || 0,
    maxAttempts: config.maxAttempts || 3,
    submitting: false,
    error: null,

    get inputsLocked() {
        return this.passed || this.attemptsUsed >= this.maxAttempts;
    },

    submit() {
        this.submitting = true;
        this.error = null;
        const wasChecked = this.checked;

        fetch(config.checkUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ answers: this.answers }),
        })
            .then((response) => {
                if (!response.ok) throw new Error('Could not check your answers. Please try again.');
                return response.json();
            })
            .then((data) => {
                this.score = data.score;
                this.passed = data.passed;
                this.feedback = Object.fromEntries(data.feedback.map((item) => [item.question_id, item]));
                this.checked = true;
                this.attemptsUsed = data.attempts_used ?? this.attemptsUsed + 1;
                this.maxAttempts = data.max_attempts ?? this.maxAttempts;

                // The course architecture now inlines a lesson's questions
                // into the level list rather than a separate activity page —
                // submitting an attempt for the first time is what "finishing"
                // this step means there, so move on to the next one. Retrying
                // (wasChecked already true) never re-triggers it.
                if (! wasChecked && config.advance) {
                    this.$dispatch('lesson-advance', config.advance);
                }
            })
            .catch((err) => {
                this.error = err.message;
            })
            .finally(() => {
                this.submitting = false;
            });
    },
}));

Alpine.start();

// After saving a lesson/question form that redirects back with a #hash,
// reopen the <details> it lives in (and any ancestor <details>) and scroll
// to it, so the admin doesn't lose their place in a long page.
document.addEventListener('DOMContentLoaded', () => {
    if (! location.hash) return;

    let target;
    try {
        target = document.querySelector(location.hash);
    } catch (e) {
        return;
    }
    if (! target) return;

    let node = target;
    while (node) {
        if (node.tagName === 'DETAILS') node.open = true;
        node = node.parentElement;
    }

    target.scrollIntoView({ block: 'center' });
});
