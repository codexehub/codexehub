<section class="quick-highlights-wrap">
    <div class="container-xxl">
        <div class="quick-highlights" data-aos="fade-up">
            @foreach ([
                ['icon' => 'bi-people', 'title' => 'Expert Team', 'desc' => 'Skilled professionals delivering top quality.'],
                ['icon' => 'bi-diagram-3', 'title' => 'Scalable Solutions', 'desc' => 'Build for today, scale for tomorrow.'],
                ['icon' => 'bi-shield-check', 'title' => 'Secure &amp; Reliable', 'desc' => 'Security and performance are our priority.'],
                ['icon' => 'bi-lightning-charge', 'title' => 'On-Time Delivery', 'desc' => 'We follow agile and deliver on commitment.'],
                ['icon' => 'bi-eye', 'title' => 'Transparent Process', 'desc' => 'Clear communication at every step.'],
                ['icon' => 'bi-headset', 'title' => 'Dedicated Support', 'desc' => '24/7 support even after delivery.'],
            ] as $item)
                <div class="highlight-tile">
                    <div class="icon-tile icon-circle"><i class="bi {{ $item['icon'] }}"></i></div>
                    <div>
                        <h4>{!! $item['title'] !!}</h4>
                        <p>{{ $item['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
