<section class="section-sm" id="service-details-cta">
    <div class="container-xxl">
        <div class="cta-band d-flex flex-column flex-lg-row align-items-center justify-content-between gap-4" data-aos="zoom-in">
            <div class="d-flex align-items-center gap-3 text-center text-lg-start">
                <div class="cta-icon d-none d-sm-flex"><i class="bi bi-rocket-takeoff"></i></div>
                <div>
                    <h2 class="text-balance mb-1">Ready to Start Your <em>Project</em>?</h2>
                    <p class="mb-0">Let's discuss how our {{ strtolower($service->title) }} service can help your business grow.</p>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-3 flex-shrink-0">
                <a href="{{ request()->routeIs('home') ? '#footer-contact' : route('home').'#footer-contact' }}" class="btn btn-accent btn-lg">Start Your Project <i class="bi bi-arrow-right"></i></a>
                <a href="tel:+8801234567890" class="btn btn-outline-light-2 btn-lg"><i class="bi bi-telephone"></i> Schedule a Call</a>
            </div>
            <i class="bi bi-rocket-takeoff mini-cta-decor"></i>
        </div>
    </div>
</section>
