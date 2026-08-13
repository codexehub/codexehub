@php
    $homeProducts = \App\Models\Product::query()
        ->active()
        ->software()
        ->featured()
        ->with('category')
        ->orderBy('sort_order')
        ->limit(3)
        ->get();
@endphp

@if ($homeProducts->isNotEmpty())
    <section class="section" id="products">
        <div class="container-xxl">
            <div class="section-heading mx-auto text-center mb-5" data-aos="fade-up">
                <span class="section-eyebrow justify-content-center">Our Products</span>
                <h2 class="text-balance">Ready-made software you can run this week</h2>
                <p class="lead-text mt-3">Alongside custom builds, we ship our own products &mdash; ERP, POS, HR and support platforms already running in real businesses.</p>
            </div>

            <div class="row g-4">
                @foreach ($homeProducts as $i => $product)
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $i * 80 }}">
                        <div class="service-card h-100">
                            @if ($product->image_url)
                                <div class="service-thumb"><img src="{{ $product->image_url }}" alt="{{ $product->title }}"></div>
                            @endif
                            <div class="service-card-body">
                                @unless ($product->image_url)
                                    <div class="icon-tile"><i class="bi {{ $product->icon }}"></i></div>
                                @endunless

                                @if ($product->category)
                                    <span class="badge-soft d-inline-block mb-2">{{ $product->category->name }}</span>
                                @endif

                                <h3>{{ $product->title }}</h3>
                                <p>{{ $product->short_description }}</p>

                                <div class="product-price-row">
                                    <span class="product-price">{{ $product->price_label }}</span>
                                    @if ($product->version)
                                        <span class="product-version">v{{ $product->version }}</span>
                                    @endif
                                </div>

                                <a href="{{ route('products.show', $product) }}" class="btn-link-arrow">View Details <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-5" data-aos="fade-up">
                <a href="{{ route('products.software') }}" class="btn btn-primary">View All Products <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </section>
@endif
