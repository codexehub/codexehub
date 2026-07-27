<section class="section-sm" id="cta">
    <div class="container-xxl">
        <div class="cta-band d-flex flex-column flex-lg-row align-items-center justify-content-between gap-4" data-aos="zoom-in">
            <div class="d-flex align-items-center gap-3 text-center text-lg-start">
                <div class="cta-icon d-none d-sm-flex"><i class="bi bi-rocket-takeoff"></i></div>
                <div>
                    <h2 class="text-balance mb-1">Ready to Start Your Next Project?</h2>
                    <p class="mb-0">Let's discuss your ideas and build something amazing together.</p>
                </div>
            </div>
            <a href="{{ request()->routeIs('home') ? '#footer-contact' : route('home').'#footer-contact' }}" class="btn btn-accent btn-lg flex-shrink-0">Get Free Consultation <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>
