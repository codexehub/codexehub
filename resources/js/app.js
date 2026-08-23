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
import { initTeamSlider } from './modules/team-slider';
import { initGallerySlider } from './modules/gallery-slider';
import { initRecentWorkSlider } from './modules/recent-work-slider';
import { initAos } from './modules/aos-init';
import { initLightbox } from './modules/lightbox-init';
import { initPortfolioFilter } from './modules/portfolio-filter';
import { initConsultationModal } from './modules/consultation-modal';
import { initProcessTimeline } from './modules/process-timeline';
import { initContactForm } from './modules/contact-form';
import { initTeamModal } from './modules/team-modal';

document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initHeroSlider();
    initTestimonialsSlider();
    initTeamSlider();
    initGallerySlider();
    initRecentWorkSlider();
    initAos();
    initLightbox();
    initPortfolioFilter();
    initConsultationModal();
    initProcessTimeline();
    initContactForm();
    initTeamModal();
});
