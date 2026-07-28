<section class="section-sm">
    <div class="container-xxl">
        <div class="about-stats-row" data-aos="fade-up">
            @foreach ([
                ['icon' => 'bi-kanban', 'value' => '150+', 'label' => 'Projects Completed'],
                ['icon' => 'bi-emoji-smile', 'value' => '75+', 'label' => 'Happy Clients'],
                ['icon' => 'bi-award', 'value' => '8+', 'label' => 'Years of Experience'],
                ['icon' => 'bi-people', 'value' => '20+', 'label' => 'Team Members'],
                ['icon' => 'bi-hand-thumbs-up', 'value' => '99%', 'label' => 'Client Satisfaction'],
                ['icon' => 'bi-headset', 'value' => '24/7', 'label' => 'Support Available'],
            ] as $stat)
                <div class="about-stat-item">
                    <div class="icon-tile icon-circle"><i class="bi {{ $stat['icon'] }}"></i></div>
                    <strong>{{ $stat['value'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
