<section class="hero project-hero">
    <div class="container-xxl">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7">
                <nav class="hero-breadcrumb mb-3" aria-label="breadcrumb">
                    <i class="bi bi-house-door-fill"></i>
                    <a href="{{ route('home') }}">Home</a>
                    <i class="bi bi-chevron-right"></i>
                    <a href="{{ route('services') }}">Services</a>
                    <i class="bi bi-chevron-right"></i>
                    <span>{{ $service->title }}</span>
                </nav>

                <h1 class="text-balance">{{ $service->title }}</h1>
                <p class="lead-text">{{ $service->short_description }}</p>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="#footer-contact" class="btn btn-accent btn-lg">Get Free Consultation <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="project-hero-visual" data-aos="fade-left" data-aos-delay="150">
                    <div class="project-hero-frame">
                        @if ($service->image_url)
                            <div class="mockup-bar"><span></span><span></span><span></span></div>
                            <img src="{{ $service->image_url }}" alt="{{ $service->title }}">
                        @else
                            <div class="project-hero-frame-fallback"><i class="bi {{ $service->icon }}"></i></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
