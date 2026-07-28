<section class="stats-bar">
    <div class="container-xxl">
        <div class="stats-bar-inner" data-aos="fade-up">
            @foreach ([
                ['icon' => 'bi-kanban', 'value' => '150+', 'label' => 'Projects Completed'],
                ['icon' => 'bi-emoji-smile', 'value' => '75+', 'label' => 'Happy Clients'],
                ['icon' => 'bi-diagram-3', 'value' => '20+', 'label' => 'Industries Served'],
                ['icon' => 'bi-award', 'value' => '8+', 'label' => 'Years of Experience'],
                ['icon' => 'bi-hand-thumbs-up', 'value' => '99%', 'label' => 'Client Satisfaction'],
                ['icon' => 'bi-headset', 'value' => '24/7', 'label' => 'Support Available'],
            ] as $stat)
                <div class="stat-item">
                    <i class="bi {{ $stat['icon'] }}"></i>
                    <strong>{{ $stat['value'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
