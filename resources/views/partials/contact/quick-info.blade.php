<div class="quick-contact-wrap">
    <div class="container-xxl">
        <div class="quick-contact-grid" data-aos="fade-up">
            @foreach ([
                ['icon' => 'bi-telephone-fill', 'label' => 'Call Us', 'value' => '+880 1234-567890', 'note' => 'Sun - Thu (8AM - 7PM)', 'href' => 'tel:+8801234567890'],
                ['icon' => 'bi-envelope-fill', 'label' => 'Email Us', 'value' => 'info@codexehub.com', 'note' => 'We reply within 24 hours', 'href' => 'mailto:info@codexehub.com'],
                ['icon' => 'bi-geo-alt-fill', 'label' => 'Visit Us', 'value' => 'House-23, Road-5, Dhanmondi', 'note' => 'Dhaka-1205, Bangladesh', 'href' => null],
                ['icon' => 'bi-clock-fill', 'label' => 'Support Hours', 'value' => 'Sun - Thu : 8AM - 7PM', 'note' => 'Friday : 8AM - 1PM', 'href' => null],
            ] as $item)
                <div class="quick-contact-item">
                    <div class="icon-tile icon-circle icon-circle-dark"><i class="bi {{ $item['icon'] }}"></i></div>
                    <div>
                        <span class="quick-contact-label">{{ $item['label'] }}</span>
                        @if ($item['href'])
                            <strong><a href="{{ $item['href'] }}">{{ $item['value'] }}</a></strong>
                        @else
                            <strong>{{ $item['value'] }}</strong>
                        @endif
                        <small>{{ $item['note'] }}</small>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
