import GLightbox from 'glightbox';

export function initLightbox() {
    if (!document.querySelector('.glightbox')) return;

    GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
}
