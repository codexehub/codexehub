@php
    $homeServices = \App\Models\Service::query()->active()->featured()->orderBy('sort_order')->get();
@endphp

@if ($homeServices->isNotEmpty())
    <section class="section" id="services">
        <div class="container-xxl">
            <div class="section-heading mx-auto text-center mb-5" data-aos="fade-up">
                <span class="section-eyebrow justify-content-center">What We Do</span>
                <h2 class="text-balance">Services built around your roadmap</h2>
            </div>

            <div class="row g-4">
                @foreach ($homeServices as $i => $service)
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $i * 60 }}">
                        <div class="service-card" id="service-{{ $service->slug }}">
                            <div class="icon-tile"><i class="bi {{ $service->icon }}"></i></div>
                            <h3>{{ $service->title }}</h3>
                            <p>{{ $service->short_description }}</p>
                            <a href="#footer-contact" class="btn-link-arrow">Learn More <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
