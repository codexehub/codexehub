import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';

export function initHeroSlider() {
    const el = document.querySelector('.hero-swiper');
    if (!el) return;

    new Swiper(el, {
        modules: [Navigation, Pagination, Autoplay],
        loop: true,
        speed: 700,
        autoplay: { delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true },
        pagination: { el: '.hero-swiper .swiper-pagination', clickable: true },
        navigation: { prevEl: '.hero-prev', nextEl: '.hero-next' },
    });
}
