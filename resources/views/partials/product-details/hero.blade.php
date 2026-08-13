<section class="hero project-hero">
    <div class="container-xxl">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7">
                <nav class="hero-breadcrumb mb-3" aria-label="breadcrumb">
                    <i class="bi bi-house-door-fill"></i>
                    <a href="{{ route('home') }}">Home</a>
                    <i class="bi bi-chevron-right"></i>
                    <a href="{{ route('products.software') }}">Products</a>
                    <i class="bi bi-chevron-right"></i>
                    <span>{{ $product->title }}</span>
                </nav>

                @if ($product->category)
                    <span class="project-category-badge"><i class="bi bi-tag-fill"></i> {{ $product->category->name }}</span>
                @endif

                <h1 class="text-balance">{{ $product->title }}</h1>
                @if ($product->tagline)
                    <p class="lead-text">{{ $product->tagline }}</p>
                @endif
                <p class="lead-text">{{ $product->short_description }}</p>

                <div class="product-hero-meta">
                    <span><i class="bi bi-tag"></i> {{ $product->price_label }}</span>
                    @if ($product->version)
                        <span><i class="bi bi-box"></i> Version {{ $product->version }}</span>
                    @endif
                    @if ($product->pricing_note)
                        <span><i class="bi bi-info-circle"></i> {{ $product->pricing_note }}</span>
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <button type="button" class="btn btn-accent btn-lg" data-bs-toggle="modal" data-bs-target="#consultationModal">Request a Demo <i class="bi bi-arrow-right"></i></button>
                    @if ($product->demo_url)
                        <a href="{{ $product->demo_url }}" class="btn btn-outline-light-2 btn-lg" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Live Preview</a>
                    @endif
                    @if ($product->docs_url)
                        <a href="{{ $product->docs_url }}" class="btn btn-outline-light-2 btn-lg" target="_blank" rel="noopener"><i class="bi bi-journal-text"></i> Documentation</a>
                    @endif
                </div>
            </div>

            <div class="col-lg-5">
                <div class="project-hero-visual" data-aos="fade-left" data-aos-delay="150">
                    <div class="project-hero-frame">
                        @if ($product->image_url)
                            <div class="mockup-bar"><span></span><span></span><span></span></div>
                            <img src="{{ $product->image_url }}" alt="{{ $product->title }}">
                        @else
                            <div class="project-hero-frame-fallback"><i class="bi {{ $product->icon }}"></i></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
