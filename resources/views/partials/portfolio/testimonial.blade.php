@php
    $quote = $portfolio->testimonial_quote
        ?: "CodexeHub delivered an exceptional {$portfolio->category->name} solution that exceeded our expectations. Their team was professional, responsive and highly skilled.";
    $name = $portfolio->testimonial_name ?: ($portfolio->client_name ?: 'Client Feedback');
    $role = $portfolio->testimonial_role ?: $portfolio->category->name;
    $rating = $portfolio->testimonial_rating ?: 5;
@endphp

<section class="section bg-soft">
    <div class="container-xxl">
        <div class="testimonial-card-dark" data-aos="fade-up">
            <div class="d-flex align-items-start gap-3">
                <div class="quote-mark"><i class="bi bi-quote"></i></div>
                <div class="testimonial-content">
                    <blockquote>{{ $quote }}</blockquote>
                </div>
            </div>
            <div class="testimonial-author">
                @if ($portfolio->testimonial_avatar_url)
                    <img src="{{ $portfolio->testimonial_avatar_url }}" alt="{{ $name }}" class="avatar-initials" style="object-fit: cover;">
                @else
                    <div class="avatar-initials">{{ Str::of($name)->explode(' ')->map(fn ($w) => Str::substr($w, 0, 1))->take(2)->implode('') }}</div>
                @endif
                <div>
                    <strong>{{ $name }}</strong>
                    <span>{{ $role }}</span>
                    <div class="rating">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= $rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
