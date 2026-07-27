import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';

export function initTestimonialsSlider() {
    const el = document.querySelector('.testimonial-swiper');
    if (!el) return;

    new Swiper(el, {
        modules: [Navigation, Pagination, Autoplay],
        loop: true,
        speed: 600,
        slidesPerView: 1,
        autoplay: { delay: 5500, disableOnInteraction: false },
        pagination: { el: '.testimonial-pagination', clickable: true },
        navigation: { prevEl: '.testimonial-prev', nextEl: '.testimonial-next' },
    });
}
