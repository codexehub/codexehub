import './bootstrap';
import * as bootstrapJs from 'bootstrap';
window.bootstrap = bootstrapJs;

import 'bootstrap-icons/font/bootstrap-icons.css';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'aos/dist/aos.css';
import 'glightbox/dist/css/glightbox.css';

import { initHeader } from './modules/header';
import { initHeroSlider } from './modules/hero-slider';
import { initTestimonialsSlider } from './modules/testimonials-slider';
import { initAos } from './modules/aos-init';
import { initLightbox } from './modules/lightbox-init';
import { initPortfolioFilter } from './modules/portfolio-filter';

document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initHeroSlider();
    initTestimonialsSlider();
    initAos();
    initLightbox();
    initPortfolioFilter();
});
