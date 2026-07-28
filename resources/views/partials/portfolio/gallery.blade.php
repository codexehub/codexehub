@php
    $galleryImages = $portfolio->galleryImages;
@endphp

<section class="section">
    <div class="container-xxl">
        <div class="section-heading mb-5" data-aos="fade-up">
            <span class="section-eyebrow">Gallery</span>
            <h2 class="text-balance">Project Screenshots</h2>
        </div>

        <div class="gallery-swiper-wrap">
            <button type="button" class="swiper-arrow gallery-prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>

            <div class="swiper gallery-swiper">
                <div class="swiper-wrapper">
                    @forelse ($galleryImages as $image)
                        <div class="swiper-slide">
                            <a href="{{ $image->url }}" class="gallery-frame glightbox" data-gallery="project-gallery">
                                <img src="{{ $image->url }}" alt="{{ $portfolio->title }} screenshot" loading="lazy">
                            </a>
                        </div>
                    @empty
                        <div class="swiper-slide">
                            <div class="gallery-frame gallery-mockup">
                                <div class="gm-bar"><span></span><span></span><span></span></div>
                                <div class="gm-body">
                                    <div class="gm-side"></div>
                                    <div class="gm-main">
                                        <div class="gm-line"></div>
                                        <div class="gm-chart"><span></span><span></span><span></span><span></span><span></span></div>
                                        <div class="gm-rows"><span></span><span></span><span></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-frame gallery-mockup">
                                <div class="gm-bar"><span></span><span></span><span></span></div>
                                <div class="gm-body">
                                    <div class="gm-main">
                                        <div class="gm-line"></div>
                                        <div class="gm-grid"><span></span><span></span><span></span></div>
                                        <div class="gm-rows"><span></span><span></span><span></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-frame gallery-mockup">
                                <div class="gm-bar"><span></span><span></span><span></span></div>
                                <div class="gm-body">
                                    <div class="gm-main">
                                        <div class="gm-line"></div>
                                        <div class="gm-chart"><span></span><span></span><span></span><span></span><span></span><span></span></div>
                                        <div class="gm-line gm-line-sm"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-frame gallery-mockup">
                                <div class="gm-bar"><span></span><span></span><span></span></div>
                                <div class="gm-body">
                                    <div class="gm-side"></div>
                                    <div class="gm-main">
                                        <div class="gm-line"></div>
                                        <div class="gm-rows"><span></span><span></span><span></span></div>
                                        <div class="gm-grid"><span></span><span></span><span></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-frame gallery-mockup">
                                <div class="gm-bar"><span></span><span></span><span></span></div>
                                <div class="gm-body">
                                    <div class="gm-main">
                                        <div class="gm-line"></div>
                                        <div class="gm-line gm-line-sm"></div>
                                        <div class="gm-rows"><span></span><span></span><span></span></div>
                                        <div class="gm-chart"><span></span><span></span><span></span><span></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="gallery-frame gallery-mockup">
                                <div class="gm-bar"><span></span><span></span><span></span></div>
                                <div class="gm-body">
                                    <div class="gm-side"></div>
                                    <div class="gm-main">
                                        <div class="gm-line"></div>
                                        <div class="gm-grid"><span></span><span></span><span></span></div>
                                        <div class="gm-line gm-line-sm"></div>
                                        <div class="gm-rows"><span></span><span></span><span></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
                <div class="swiper-pagination gallery-pagination"></div>
            </div>

            <button type="button" class="swiper-arrow gallery-next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
        </div>
    </div>
</section>
