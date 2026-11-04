import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('lessonMediaUploader', () => ({
    uploads: [],
    nextKey: 1,
    lightboxUrl: null,
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

Alpine.data('lessonVideoTracker', (resumeSeconds, progressUrl) => ({
    hasVideo: false,
    watchedPercent: 0,
    lastSent: 0,

    get videoSatisfied() {
        return ! this.hasVideo || this.watchedPercent >= 0.98;
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
