@php
    $homeServices = \App\Models\Service::query()->active()->featured()->orderBy('sort_order')->get();
@endphp

@if ($homeServices->isNotEmpty())
    <section class="section bg-primary-tint services-textured" id="services">
        <div class="container-xxl">
            <div class="section-heading mx-auto text-center mb-5" data-aos="fade-up">
                <span class="section-eyebrow justify-content-center">What We Do</span>
                <h2 class="text-balance">Services built around your roadmap</h2>
                <p class="lead-text mt-3">Pick a track and plug straight in — from a single feature to a full product build, every engagement is scoped around where you want to be next.</p>
            </div>

            <div class="row g-4">
                @foreach ($homeServices as $i => $service)
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $i * 60 }}">
                        <div class="service-card{{ $i === 1 ? ' is-featured' : '' }}" id="service-{{ $service->slug }}">
                            @if ($i === 1)
                                <span class="service-badge"><i class="bi bi-stars"></i> Most Popular</span>
                            @elseif (! $service->image_url)
                                <span class="service-index">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            @endif
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

            <div class="text-center mt-5" data-aos="fade-up">
                <a href="{{ route('services') }}" class="btn btn-primary">View All Services <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </section>
@endif
