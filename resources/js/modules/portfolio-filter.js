import Isotope from 'isotope-layout';

export function initPortfolioFilter() {
    const grid = document.querySelector('.portfolio-grid');
    const filters = document.querySelector('.portfolio-filters');
    if (!grid || !filters) return;

    const iso = new Isotope(grid, {
        itemSelector: '.portfolio-item',
        layoutMode: 'fitRows',
        transitionDuration: '0.4s',
    });

    filters.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-filter]');
        if (!button) return;

        filters.querySelectorAll('button').forEach((btn) => btn.classList.remove('is-active'));
        button.classList.add('is-active');
        iso.arrange({ filter: button.dataset.filter });
    });
}
