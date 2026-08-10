import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';

export function initRecentWorkSlider() {
    const el = document.querySelector('.recent-work-swiper');
    if (!el) return;

    new Swiper(el, {
        modules: [Navigation, Pagination],
        speed: 500,
        spaceBetween: 20,
        slidesPerView: 1.1,
        pagination: { el: '.recent-work-pagination', clickable: true },
        navigation: { prevEl: '.recent-work-prev', nextEl: '.recent-work-next' },
        breakpoints: {
            480: { slidesPerView: 1.4 },
            768: { slidesPerView: 2 },
            1200: { slidesPerView: 2.4 },
            1400: { slidesPerView: 3 },
        },
    });
}
