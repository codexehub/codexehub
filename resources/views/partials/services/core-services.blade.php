@php
    $coreServices = \App\Models\Service::query()->active()->orderBy('sort_order')->get();
@endphp

<section class="section" id="core-services">
    <div class="container-xxl">
        <div class="section-heading mx-auto text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow justify-content-center">What We Do</span>
            <h2 class="text-balance">Our Core Services</h2>
            <p class="mx-auto">We provide a wide range of digital services to help startups, enterprises and organizations turn ideas into powerful digital products.</p>
        </div>

        <div class="row g-4">
            @foreach ($coreServices as $i => $service)
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ $i * 60 }}">
                    <div class="service-card" id="service-{{ $service->slug }}">
                        @unless ($service->image_url)
                            <span class="service-index">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        @endunless
                        @if ($service->image_url)
                            <div class="service-thumb"><img src="{{ $service->image_url }}" alt="{{ $service->title }}"></div>
                        @endif
                        <div class="service-card-body">
                            @unless ($service->image_url)
                                <div class="icon-tile"><i class="bi {{ $service->icon }}"></i></div>
                            @endunless
                            <h3>{{ $service->title }}</h3>
                            <p>{{ $service->short_description }}</p>
                            <a href="{{ route('services.show', $service) }}" class="btn-link-arrow">Learn More <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
