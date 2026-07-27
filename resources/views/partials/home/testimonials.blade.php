@php
    $testimonials = [
        ['name' => 'Mohammad Rahman', 'role' => 'CEO, Dhroh Solutions', 'initials' => 'MR', 'quote' => 'CodexeHub delivered an amazing solution for our business. Their team is professional, skilled and always delivers high-quality work on time.'],
        ['name' => 'Sarah Malik', 'role' => 'CTO, Nimbus', 'initials' => 'SM', 'quote' => 'CodexeHub rebuilt our billing platform in under four months with zero downtime during migration. The team communicates like an in-house squad.'],
        ['name' => 'David Okoro', 'role' => 'Founder, Orbitel', 'initials' => 'DO', 'quote' => 'Our field app went from concept to 300 active technicians in a single quarter. Every sprint demo shipped exactly what was promised.'],
    ];
@endphp

<section class="section" id="testimonials">
    <div class="container-xxl">
        <div class="section-heading mx-auto text-center mb-5" data-aos="fade-up">
            <span class="section-eyebrow justify-content-center">Testimonials</span>
            <h2 class="text-balance">What Our Clients Say</h2>
        </div>

        <div class="testimonial-wrap position-relative">
            <button type="button" class="swiper-arrow testimonial-prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>

            <div class="swiper testimonial-swiper">
                <div class="swiper-wrapper">
                    @foreach ($testimonials as $t)
                        <div class="swiper-slide">
                            <div class="testimonial-card">
                                <div class="quote-mark"><i class="bi bi-quote"></i></div>
                                <div class="testimonial-content">
                                    <blockquote>{{ $t['quote'] }}</blockquote>
                                </div>
                                <div class="testimonial-author">
                                    <div class="avatar-initials">{{ $t['initials'] }}</div>
                                    <div>
                                        <strong>{{ $t['name'] }}</strong>
                                        <span>{{ $t['role'] }}</span>
                                        <div class="rating">
                                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination testimonial-pagination"></div>
            </div>

            <button type="button" class="swiper-arrow testimonial-next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
        </div>
    </div>
</section>
