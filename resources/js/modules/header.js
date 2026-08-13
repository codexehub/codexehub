export function initHeader() {
    const header = document.querySelector('.site-header');
    if (!header) return;

    const onScroll = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
    };

    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    // A single button can't both dismiss an offcanvas and open a modal — Bootstrap's
    // dismiss handler reads the shared data-bs-target and aims at the modal. Close any
    // open drawer whenever a modal opens so its backdrop doesn't stack under the modal.
    // `.show` alone would miss a drawer that is still mid-open-transition (`.showing`),
    // so hide every instantiated offcanvas — Bootstrap no-ops on already-hidden ones.
    document.addEventListener('show.bs.modal', () => {
        document.querySelectorAll('.offcanvas').forEach((el) => {
            window.bootstrap?.Offcanvas.getInstance(el)?.hide();
        });
    });
}
