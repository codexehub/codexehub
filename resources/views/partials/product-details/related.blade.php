@if ($relatedProducts->isNotEmpty())
    <section class="section">
        <div class="container-xxl">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-5" data-aos="fade-up">
                <div class="section-heading mb-0">
                    <span class="section-eyebrow">Explore More</span>
                    <h2 class="text-balance mb-0">Related Products</h2>
                </div>
                <a href="{{ route('products.software') }}" class="btn-link-arrow">View All Products <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="row g-4">
                @foreach ($relatedProducts as $i => $related)
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $i * 80 }}">
                        <div class="service-card h-100">
                            @if ($related->image_url)
                                <div class="service-thumb"><img src="{{ $related->image_url }}" alt="{{ $related->title }}"></div>
                            @endif
                            <div class="service-card-body">
                                @unless ($related->image_url)
                                    <div class="icon-tile"><i class="bi {{ $related->icon }}"></i></div>
                                @endunless
                                <h3>{{ $related->title }}</h3>
                                <p>{{ $related->short_description }}</p>
                                <div class="product-price-row">
                                    <span class="product-price">{{ $related->price_label }}</span>
                                </div>
                                <a href="{{ route('products.show', $related) }}" class="btn-link-arrow">View Details <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
