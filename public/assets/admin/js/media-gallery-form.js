(function () {
    'use strict';

    const form = document.getElementById('mediaGalleryForm');
    if (!form) return;

    const limits = window.mediaGalleryLimits || { image: 5, video: 100 };
    const typeInputs = Array.from(form.querySelectorAll('input[name="media_type"]'));
    const mediaFile = form.querySelector('[data-media-file]');
    const fileLabel = form.querySelector('[data-file-label]');
    const fileHelp = form.querySelector('[data-file-help]');
    const thumbnailGroup = form.querySelector('[data-thumbnail-group]');
    const preview = form.querySelector('[data-media-preview]');
    const aspectSelect = form.querySelector('[data-aspect-select]');
    const durationInput = form.querySelector('[data-duration-input]');
    let objectUrl = null;

    const selectedType = function () {
        const selected = typeInputs.find(function (input) { return input.checked; });
        return selected ? selected.value : 'image';
    };

    const revokePreviewUrl = function () {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
    };

    const resetPreview = function () {
        revokePreviewUrl();
        if (preview) {
            preview.innerHTML = '';
            preview.hidden = true;
        }
        if (durationInput) durationInput.value = '';
    };

    const syncType = function (resetFile) {
        const mediaType = selectedType();

        if (mediaFile) {
            mediaFile.accept = mediaType === 'video'
                ? 'video/mp4,video/webm'
                : 'image/jpeg,image/png,image/webp';

            if (resetFile) {
                mediaFile.value = '';
                resetPreview();
            }
        }

        if (fileLabel) {
            fileLabel.textContent = mediaType === 'video' ? 'File Video Reels' : 'File Foto';
        }

        if (fileHelp) {
            fileHelp.textContent = mediaType === 'video'
                ? 'MP4 atau WEBM. Maksimal ' + limits.video + ' MB. Gunakan portrait 9:16 untuk hasil terbaik.'
                : 'JPG, PNG, atau WEBP. Maksimal ' + limits.image + ' MB.';
        }

        if (thumbnailGroup) thumbnailGroup.hidden = mediaType !== 'video';

        if (aspectSelect && resetFile) {
            aspectSelect.value = mediaType === 'video' ? 'portrait' : 'landscape';
        }
    };

    const validateClientSize = function (file, mediaType) {
        const maxMb = mediaType === 'video' ? Number(limits.video) : Number(limits.image);
        const maxBytes = maxMb * 1024 * 1024;

        if (file.size <= maxBytes) return true;

        window.alert('Ukuran file terlalu besar. Maksimal ' + maxMb + ' MB.');
        if (mediaFile) mediaFile.value = '';
        resetPreview();
        return false;
    };

    typeInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            syncType(true);
        });
    });

    if (mediaFile) {
        mediaFile.addEventListener('change', function () {
            resetPreview();

            const file = mediaFile.files && mediaFile.files[0];
            if (!file || !preview) return;

            const mediaType = selectedType();
            if (!validateClientSize(file, mediaType)) return;

            objectUrl = URL.createObjectURL(file);

            if (mediaType === 'video') {
                const video = document.createElement('video');
                video.src = objectUrl;
                video.controls = true;
                video.muted = true;
                video.playsInline = true;
                video.preload = 'metadata';
                video.addEventListener('loadedmetadata', function () {
                    if (durationInput && Number.isFinite(video.duration)) {
                        durationInput.value = String(Math.round(video.duration));
                    }
                });
                preview.appendChild(video);
            } else {
                const image = document.createElement('img');
                image.src = objectUrl;
                image.alt = 'Preview media';
                preview.appendChild(image);
            }

            preview.hidden = false;
        });
    }

    form.addEventListener('submit', function () {
        const submitButton = form.querySelector('button[type="submit"]');
        if (!submitButton) return;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Mengupload media...';
    });

    window.addEventListener('beforeunload', revokePreviewUrl);
    syncType(false);
})();
