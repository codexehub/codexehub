<section class="section-sm bg-soft">
    <div class="container-xxl">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-7">
                <div class="mini-panel" data-aos="fade-up">
                    <h4>Why Contact CodexeHub?</h4>
                    <p class="mini-panel-subtitle">We are committed to providing the best solutions for your business.</p>

                    <div class="why-us-grid">
                        @foreach ([
                            ['icon' => 'bi-people', 'title' => 'Experienced Team', 'desc' => 'Skilled professionals with years of industry experience.'],
                            ['icon' => 'bi-sliders', 'title' => 'Custom Solutions', 'desc' => 'Tailored solutions designed to meet your unique needs.'],
                            ['icon' => 'bi-clock-history', 'title' => 'On-Time Delivery', 'desc' => 'We value time and ensure timely project delivery.'],
                            ['icon' => 'bi-tags', 'title' => 'Affordable Pricing', 'desc' => 'High-quality services at competitive prices.'],
                            ['icon' => 'bi-headset', 'title' => '24/7 Support', 'desc' => "We're always here to assist you at any time."],
                            ['icon' => 'bi-handshake', 'title' => 'Long-term Partnership', 'desc' => 'We build long-term relationships with our clients.'],
                        ] as $item)
                            <div class="why-us-item">
                                <div class="icon-tile icon-circle"><i class="bi {{ $item['icon'] }}"></i></div>
                                <div>
                                    <h5>{{ $item['title'] }}</h5>
                                    <p>{{ $item['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="mini-panel" data-aos="fade-up" data-aos-delay="100">
                    <h4>Quick Contact</h4>
                    <p class="mini-panel-subtitle">Prefer instant communication? Reach out to us through any of these channels.</p>

                    <div class="quick-channel-list">
                        <a href="https://wa.me/8801234567890" target="_blank" rel="noopener" class="quick-channel-item">
                            <div class="icon-tile icon-circle"><i class="bi bi-whatsapp"></i></div>
                            <div class="quick-channel-copy">
                                <strong>WhatsApp</strong>
                                <span>+880 1234-567890</span>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="https://t.me/codexehub" target="_blank" rel="noopener" class="quick-channel-item">
                            <div class="icon-tile icon-circle"><i class="bi bi-telegram"></i></div>
                            <div class="quick-channel-copy">
                                <strong>Telegram</strong>
                                <span>@codexehub</span>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="skype:codexehub?chat" class="quick-channel-item">
                            <div class="icon-tile icon-circle"><i class="bi bi-skype"></i></div>
                            <div class="quick-channel-copy">
                                <strong>Skype</strong>
                                <span>codexehub</span>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
