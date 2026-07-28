import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';

export function initGallerySlider() {
    const el = document.querySelector('.gallery-swiper');
    if (!el) return;

    new Swiper(el, {
        modules: [Navigation, Pagination],
        speed: 500,
        spaceBetween: 20,
        slidesPerView: 1.15,
        pagination: { el: '.gallery-pagination', clickable: true },
        navigation: { prevEl: '.gallery-prev', nextEl: '.gallery-next' },
        breakpoints: {
            576: { slidesPerView: 2 },
            992: { slidesPerView: 2.4 },
            1200: { slidesPerView: 3 },
        },
    });
}
