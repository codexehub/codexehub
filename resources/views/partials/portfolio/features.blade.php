@php
    $features = [
        ['icon' => 'bi-shield-lock', 'title' => 'Secure Authentication', 'desc' => 'Role-based access with encrypted, secure user accounts.'],
        ['icon' => 'bi-speedometer2', 'title' => 'Admin Dashboard', 'desc' => 'Real-time insights and full control over content and data.'],
        ['icon' => 'bi-search', 'title' => 'Advanced Search', 'desc' => 'Smart filtering and search built for speed and accuracy.'],
        ['icon' => 'bi-credit-card', 'title' => 'Payments &amp; Integrations', 'desc' => 'Seamless third-party and payment gateway integrations.'],
        ['icon' => 'bi-bar-chart-line', 'title' => 'Analytics &amp; Reporting', 'desc' => 'Actionable reports to track growth and performance.'],
        ['icon' => 'bi-phone', 'title' => 'Responsive Design', 'desc' => 'A consistent, polished experience across every device.'],
    ];
@endphp

<section class="section bg-soft">
    <div class="container-xxl">
        <div class="section-heading mb-5" data-aos="fade-up">
            <span class="section-eyebrow">Key Features</span>
            <h2 class="text-balance">What Makes This Project Stand Out</h2>
        </div>

        <div class="feature-strip" data-aos="fade-up">
            @foreach ($features as $feature)
                <div class="feature-strip-item">
                    <i class="bi {{ $feature['icon'] }}"></i>
                    <h4>{!! $feature['title'] !!}</h4>
                    <p>{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
