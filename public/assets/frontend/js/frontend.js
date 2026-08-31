function openLoginModal() {
    const modalElement = document.getElementById('loginModal');
    if (!modalElement || typeof bootstrap === 'undefined') return;
    bootstrap.Modal.getOrCreateInstance(modalElement).show();
}

function closeLoginModal() {
    const modalElement = document.getElementById('loginModal');
    if (!modalElement || typeof bootstrap === 'undefined') return;
    bootstrap.Modal.getOrCreateInstance(modalElement).hide();
}

document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.querySelector('.site-navbar');
    const navLinks = document.querySelectorAll('.site-navbar .nav-link');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const heroVideo = document.querySelector('.wl-hero-video');

    if (heroVideo) {
        const showHeroFallback = function () {
            heroVideo.pause();
            heroVideo.style.display = 'none';
        };

        heroVideo.addEventListener('error', showHeroFallback);

        if (reducedMotion) {
            showHeroFallback();
        } else {
            heroVideo.play().catch(function () {
                // Browser dapat menolak autoplay; poster tetap menjadi fallback.
            });

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    heroVideo.pause();
                    return;
                }

                heroVideo.play().catch(function () {});
            });
        }
    }

    function handleNavbarScroll() {
        if (!navbar) return;
        navbar.classList.toggle('navbar-shrink', window.scrollY > 40);
    }

    handleNavbarScroll();
    window.addEventListener('scroll', handleNavbarScroll, { passive: true });

    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            if (!navbarCollapse || typeof bootstrap === 'undefined') return;
            const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
            if (bsCollapse) bsCollapse.hide();
        });
    });

    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (event) {
            const targetId = this.getAttribute('href');
            if (!targetId || targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (!targetElement) return;

            event.preventDefault();
            const navbarHeight = navbar ? navbar.offsetHeight : 0;
            const targetPosition = targetElement.getBoundingClientRect().top + window.scrollY - navbarHeight + 2;

            window.scrollTo({
                top: targetPosition,
                behavior: reducedMotion ? 'auto' : 'smooth'
            });
        });
    });

    const revealItems = document.querySelectorAll('[data-reveal]');
    revealItems.forEach(function (item) {
        const delay = Number(item.dataset.delay || 0);
        item.style.setProperty('--reveal-delay', delay + 'ms');
    });

    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealItems.forEach(function (item) { item.classList.add('is-visible'); });
    } else {
        const revealObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.14, rootMargin: '0px 0px -45px 0px' });

        revealItems.forEach(function (item) { revealObserver.observe(item); });
    }

    const counters = document.querySelectorAll('[data-counter]');
    const animateCounter = function (counter) {
        const target = Number(counter.dataset.counter || 0);
        const decimal = Number(counter.dataset.decimal || 0);
        const duration = 1300;
        const startedAt = performance.now();

        const tick = function (now) {
            const progress = Math.min((now - startedAt) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = target * eased;
            counter.textContent = decimal > 0
                ? (current / Math.pow(10, decimal)).toFixed(decimal)
                : Math.round(current).toLocaleString('id-ID');

            if (progress < 1) requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
    };

    if (!reducedMotion && 'IntersectionObserver' in window) {
        const counterObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.55 });

        counters.forEach(function (counter) { counterObserver.observe(counter); });
    } else {
        counters.forEach(function (counter) {
            const value = Number(counter.dataset.counter || 0);
            const decimal = Number(counter.dataset.decimal || 0);
            counter.textContent = decimal > 0
                ? (value / Math.pow(10, decimal)).toFixed(decimal)
                : value.toLocaleString('id-ID');
        });
    }

    if (!reducedMotion) {
        const parallaxElements = document.querySelectorAll('[data-parallax-speed]');
        let ticking = false;

        const updateParallax = function () {
            const scrollTop = window.scrollY;
            parallaxElements.forEach(function (element) {
                const speed = Number(element.dataset.parallaxSpeed || 0.12);
                const offset = Math.min(scrollTop * speed, 120);
                element.style.transform = 'translate3d(0,' + offset + 'px,0) scale(1.03)';
            });
            ticking = false;
        };

        window.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(updateParallax);
        }, { passive: true });

        document.querySelectorAll('[data-tilt]').forEach(function (card) {
            card.addEventListener('pointermove', function (event) {
                if (window.innerWidth < 992) return;
                const rect = card.getBoundingClientRect();
                const x = (event.clientX - rect.left) / rect.width - 0.5;
                const y = (event.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = 'perspective(900px) rotateX(' + (-y * 4) + 'deg) rotateY(' + (x * 5) + 'deg) translateY(-4px)';
            });

            card.addEventListener('pointerleave', function () {
                card.style.transform = '';
            });
        });
    }
});
