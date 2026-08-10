export function initProcessTimeline() {
    const el = document.querySelector('.process-timeline');
    if (!el) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        el.classList.add('is-visible');
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                el.classList.add('is-visible');
                observer.unobserve(el);
            }
        });
    }, { threshold: .3 });

    observer.observe(el);
}
