(function () {
    'use strict';

    const photoSlider = document.querySelector('[data-gallery-slider]');
    const photoPrev = document.querySelector('[data-gallery-prev]');
    const photoNext = document.querySelector('[data-gallery-next]');

    if (photoSlider && photoPrev && photoNext) {
        const scrollPhotos = function (direction) {
            photoSlider.scrollBy({ left: direction * photoSlider.clientWidth * 0.85, behavior: 'smooth' });
        };
        photoPrev.addEventListener('click', function () { scrollPhotos(-1); });
        photoNext.addEventListener('click', function () { scrollPhotos(1); });
    }

    const track = document.querySelector('[data-reels-track]');
    const cards = Array.from(document.querySelectorAll('[data-reel-card]'));
    const reelsPrev = document.querySelector('[data-reels-prev]');
    const reelsNext = document.querySelector('[data-reels-next]');

    if (!track || cards.length === 0) return;

    const videos = cards.map(function (card) { return card.querySelector('video'); }).filter(Boolean);

    const pauseOthers = function (activeVideo) {
        videos.forEach(function (video) {
            if (video !== activeVideo) {
                video.pause();
                const card = video.closest('[data-reel-card]');
                if (card) card.classList.remove('is-playing');
            }
        });
    };

    const setPlayingState = function (video) {
        const card = video.closest('[data-reel-card]');
        if (!card) return;

        const isPlaying = !video.paused && !video.ended;
        const playButton = card.querySelector('[data-reel-play]');
        const icon = playButton ? playButton.querySelector('i') : null;

        card.classList.toggle('is-playing', isPlaying);

        if (playButton) {
            playButton.setAttribute('aria-label', isPlaying ? 'Jeda video' : 'Putar video');
            playButton.setAttribute('aria-hidden', isPlaying ? 'true' : 'false');
            playButton.tabIndex = isPlaying ? -1 : 0;
        }

        if (icon) icon.className = 'bi bi-play-fill';
    };

    const togglePlay = function (video) {
        if (video.paused) {
            pauseOthers(video);
            video.play().catch(function () {});
        } else {
            video.pause();
        }
        setPlayingState(video);
    };

    cards.forEach(function (card) {
        const video = card.querySelector('video');
        const playButton = card.querySelector('[data-reel-play]');
        const soundButton = card.querySelector('[data-reel-sound]');

        if (!video) return;

        video.addEventListener('play', function () { setPlayingState(video); });
        video.addEventListener('pause', function () { setPlayingState(video); });
        video.addEventListener('click', function () { togglePlay(video); });

        if (playButton) {
            playButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                togglePlay(video);
            });
        }

        if (soundButton) {
            soundButton.addEventListener('click', function () {
                video.muted = !video.muted;
                const icon = soundButton.querySelector('i');
                if (icon) icon.className = video.muted ? 'bi bi-volume-mute-fill' : 'bi bi-volume-up-fill';
                soundButton.setAttribute('aria-label', video.muted ? 'Aktifkan suara' : 'Matikan suara');
                if (video.paused) togglePlay(video);
            });
        }
    });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                const video = entry.target.querySelector('video');
                if (!video) return;

                if (entry.isIntersecting && entry.intersectionRatio >= 0.72) {
                    pauseOthers(video);
                    video.muted = true;
                    const soundIcon = entry.target.querySelector('[data-reel-sound] i');
                    if (soundIcon) soundIcon.className = 'bi bi-volume-mute-fill';
                    video.play().catch(function () {});
                } else if (!entry.isIntersecting || entry.intersectionRatio < 0.35) {
                    video.pause();
                }
                setPlayingState(video);
            });
        }, { threshold: [0.25, 0.35, 0.72, 1] });

        cards.forEach(function (card) { observer.observe(card); });
    }

    const scrollReels = function (direction) {
        const firstCard = cards[0];
        const gap = parseFloat(window.getComputedStyle(track).gap || '18');
        const amount = firstCard ? firstCard.getBoundingClientRect().width + gap : track.clientWidth * 0.85;
        track.scrollBy({ left: direction * amount, behavior: 'smooth' });
    };

    if (reelsPrev) reelsPrev.addEventListener('click', function () { scrollReels(-1); });
    if (reelsNext) reelsNext.addEventListener('click', function () { scrollReels(1); });

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) videos.forEach(function (video) { video.pause(); });
    });
})();
