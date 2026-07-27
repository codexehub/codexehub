<section class="section bg-soft" id="process">
    <div class="container-xxl">
        <div class="section-heading mb-5" data-aos="fade-up">
            <span class="section-eyebrow">Our Process</span>
            <h2 class="text-balance">Our Development Process</h2>
        </div>

        <div class="process-timeline">
            @foreach ([
                ['icon' => 'bi-clipboard-check', 'title' => 'Requirement Understanding', 'desc' => 'We analyze your needs and define the project scope.'],
                ['icon' => 'bi-file-earmark-text', 'title' => 'Planning', 'desc' => 'We create a detailed plan and strategy for your project.'],
                ['icon' => 'bi-pencil-square', 'title' => 'Design', 'desc' => 'Our designers create modern and intuitive UI/UX designs.'],
                ['icon' => 'bi-code-slash', 'title' => 'Development', 'desc' => 'We build your solution using clean and efficient code.'],
                ['icon' => 'bi-patch-check', 'title' => 'Testing', 'desc' => 'We test thoroughly to ensure quality and bug-free delivery.'],
                ['icon' => 'bi-cloud-arrow-up', 'title' => 'Deployment', 'desc' => 'We deploy your solution to the live environment.'],
                ['icon' => 'bi-headset', 'title' => 'Support', 'desc' => 'We provide ongoing support and maintenance.'],
            ] as $i => $step)
                <div class="process-step" data-aos="fade-up" data-aos-delay="{{ $i * 60 }}">
                    <div class="step-icon"><i class="bi {{ $step['icon'] }}"></i></div>
                    <span class="step-number">{{ sprintf('%02d', $i + 1) }}</span>
                    <h4>{{ $step['title'] }}</h4>
                    <p>{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
