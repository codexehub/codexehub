<section class="section" id="software-products">
    <div class="container-xxl">
        <div class="section-heading mx-auto text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow justify-content-center">Our Products</span>
            <h2 class="text-balance">Software built once, ready for you now</h2>
            <p class="mx-auto">Every product below is a system we already run in production for our clients. Pick one, see it live, and we&rsquo;ll have it set up for your business.</p>
        </div>

        @if ($categories->isNotEmpty())
            <div class="product-filters mb-5" data-aos="fade-up">
                <a href="{{ route('products.software') }}" class="{{ $activeCategory ? '' : 'is-active' }}">
                    <i class="bi bi-grid"></i> All Products
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('products.software', ['category' => $category->slug]) }}" class="{{ $activeCategory?->id === $category->id ? 'is-active' : '' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="row g-4">
            @forelse ($products as $i => $product)
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 80 }}">
                    <div class="service-card h-100" id="product-{{ $product->slug }}">
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
            @empty
                <div class="col-12">
                    <p class="text-center text-secondary mb-0">No products in this category yet. <a href="{{ route('products.software') }}">View all products</a>.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
