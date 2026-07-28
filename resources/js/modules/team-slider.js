import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';

export function initTeamSlider() {
    const el = document.querySelector('.team-swiper');
    if (!el) return;

    new Swiper(el, {
        modules: [Navigation],
        speed: 500,
        spaceBetween: 24,
        slidesPerView: 1.3,
        navigation: { prevEl: '.team-prev', nextEl: '.team-next' },
        breakpoints: {
            480: { slidesPerView: 2 },
            768: { slidesPerView: 3 },
            992: { slidesPerView: 4 },
            1200: { slidesPerView: 6 },
        },
    });
}
