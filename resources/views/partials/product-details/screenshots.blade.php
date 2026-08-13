@if ($product->images->isNotEmpty())
    <section class="section bg-soft">
        <div class="container-xxl">
            <div class="section-heading mb-5" data-aos="fade-up">
                <span class="section-eyebrow">Gallery</span>
                <h2 class="text-balance">Product Screenshots</h2>
            </div>

            <div class="gallery-swiper-wrap">
                <button type="button" class="swiper-arrow gallery-prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>

                <div class="swiper gallery-swiper">
                    <div class="swiper-wrapper">
                        @foreach ($product->images as $image)
                            <div class="swiper-slide">
                                <a href="{{ $image->url }}" class="gallery-frame glightbox" data-gallery="product-gallery">
                                    <img src="{{ $image->url }}" alt="{{ $product->title }} screenshot" loading="lazy">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="button" class="swiper-arrow gallery-next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
                <div class="gallery-pagination"></div>
            </div>
        </div>
    </section>
@endif
