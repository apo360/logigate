// The standalone landing uses full-page navigation, not wire:navigate.
// Keep motion independent of the existing functional event handlers.
const instanceKey = Symbol.for('logigate.landing.motion');

export function initLandingMotion(root = document.querySelector('body.lg-landing')) {
    if (!root || root[instanceKey]) return root?.[instanceKey];

    const state = { observer: null, status: 'initializing' };
    root[instanceKey] = state;
    const targets = new Set();
    let listeners;

    function reveal(element) {
        element.removeAttribute('data-lg-pending');
        element.setAttribute('data-lg-revealed', '');
        state.observer?.unobserve(element);
    }

    function finish() {
        try { state.observer?.disconnect(); } catch { /* Best-effort observer cleanup. */ }
        targets.forEach(element => {
            element.removeAttribute('data-lg-pending');
            element.setAttribute('data-lg-revealed', '');
        });
        root.removeAttribute('data-lg-motion-ready');
        listeners?.abort();
        state.status = 'static';
    }

    try {
        const preference = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (preference.matches || typeof window.IntersectionObserver !== 'function') {
            state.status = 'static';
            return state;
        }

        const mobile = window.matchMedia('(max-width: 599px)').matches;
        const groups = [
            '.platform-heading', '.demo-frame', '.platform-support',
            '#ferramentas .intro', '.public-tools > .tool',
            '.steps-section h2', '.steps > li', '#planos .intro', '.plan-grid > .plan',
            '.portal-grid > div', '.faq-grid > div', '.contact-grid > div, .contact-grid > form',
        ];

        state.observer = new IntersectionObserver(entries => {
            try {
                entries.forEach(entry => {
                    if (entry.isIntersecting) reveal(entry.target);
                });
            } catch {
                finish();
            }
        }, { rootMargin: '0px 0px -72px 0px', threshold: 0.01 });

        groups.forEach(selector => {
            const elements = [...root.querySelectorAll(selector)];
            elements.forEach((element, index) => {
                targets.add(element);
                element.setAttribute('data-lg-reveal', '');
                // Stagger only the first 2–4 blocks of a group, never a long list.
                const delay = elements.length > 1 && index < 4 ? index * (mobile ? 60 : 70) : 0;
                element.style.setProperty('--lg-reveal-delay', `${delay}ms`);
                if (element.getBoundingClientRect().top < window.innerHeight - 72 || element.contains(document.activeElement)) {
                    reveal(element);
                } else {
                    // Observe successfully before enabling any hidden styles.
                    state.observer.observe(element);
                    element.setAttribute('data-lg-pending', '');
                }
            });
        });

        listeners = new AbortController();
        root.addEventListener('focusin', event => {
            // Keyboard focus never waits for a scroll animation or stagger delay.
            targets.forEach(element => {
                if (element.contains(event.target)) reveal(element);
            });
        }, { signal: listeners.signal });
        preference.addEventListener('change', event => {
            if (event.matches) finish();
        }, { signal: listeners.signal });

        root.setAttribute('data-lg-motion-ready', '');
        state.status = 'ready';
    } catch {
        // Motion failure must never interfere with content or functional controls.
        finish();
    }

    return state;
}
