@php
    $results = $portfolio->results->isNotEmpty()
        ? $portfolio->results
        : collect([
            ['icon' => 'bi-graph-up-arrow', 'value' => '150%', 'label' => 'Increase in Engagement'],
            ['icon' => 'bi-lightning-charge', 'value' => '80%', 'label' => 'Faster Performance'],
            ['icon' => 'bi-arrow-up-right-circle', 'value' => '45%', 'label' => 'Improvement in Conversion'],
            ['icon' => 'bi-emoji-smile', 'value' => '95%', 'label' => 'Client Satisfaction'],
        ]);
    $hasTech = $portfolio->technologies->isNotEmpty();
@endphp

<section class="section">
    <div class="container-xxl">
        <div class="row g-4">
            @if ($hasTech)
                <div class="col-lg-4" data-aos="fade-up">
                    <div class="tech-results-panel h-100">
                        <h4>Technologies Used</h4>
                        <div class="tech-chip-grid">
                            @foreach ($portfolio->technologies as $technology)
                                <span class="tech-chip"><i class="bi bi-code-slash"></i> {{ $technology->technology_name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="{{ $hasTech ? 'col-lg-8' : 'col-lg-12' }}" data-aos="fade-up" data-aos-delay="100">
                <h4 class="results-heading">Results &amp; Impact</h4>
                <div class="row g-3">
                    @foreach ($results as $result)
                        <div class="col-6 col-md-3">
                            <div class="result-stat-card h-100">
                                <div class="result-stat-icon"><i class="bi {{ is_array($result) ? $result['icon'] : $result->icon }}"></i></div>
                                <strong>{{ is_array($result) ? $result['value'] : $result->value }}</strong>
                                <span>{{ is_array($result) ? $result['label'] : $result->label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
