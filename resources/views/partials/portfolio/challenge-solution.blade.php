<section class="section bg-soft">
    <div class="container-xxl">
        <div class="row g-4">
            <div class="col-lg-6" data-aos="fade-up">
                <div class="challenge-solution-card">
                    <div class="icon-tile"><i class="bi bi-exclamation-diamond"></i></div>
                    <h3>The Challenge</h3>
                    <p>{{ $portfolio->challenge ?: 'The client needed a scalable, secure solution that could handle growing demand while delivering a fast, reliable experience for every user.' }}</p>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="challenge-solution-card">
                    <div class="icon-tile"><i class="bi bi-lightbulb"></i></div>
                    <h3>Our Solution</h3>
                    <p>{{ $portfolio->solution ?: 'We designed and built a custom platform with a modern architecture, optimized performance and a user-centric interface to help the client achieve their goals.' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
