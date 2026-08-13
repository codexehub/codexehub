<footer class="site-footer">
    <div class="container-xxl">
        <div class="row gy-5">
            <div class="col-lg-4">
                <a href="{{ url('/') }}" class="footer-brand d-inline-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-braces-asterisk"></i> CodexeHub
                </a>
                <p class="mb-4">We help businesses transform ideas into powerful digital solutions with modern technologies.</p>
                <div class="footer-social">
                    <a href="https://linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    <a href="https://upwork.com" target="_blank" rel="noopener" aria-label="Upwork"><i class="bi bi-briefcase"></i></a>
                    <a href="https://fiverr.com" target="_blank" rel="noopener" aria-label="Fiverr"><i class="bi bi-lightning-charge"></i></a>
                    <a href="https://x.com" target="_blank" rel="noopener" aria-label="X (Twitter)"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Quick Links</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('about') }}">About Us</a></li>
                    <li><a href="{{ route('services') }}">Services</a></li>
                    <li><a href="{{ route('products.software') }}">Software Products</a></li>
                    <li><a href="{{ request()->routeIs('home') ? '#projects' : route('home').'#projects' }}">Projects</a></li>
                    <li><a href="{{ route('portfolio') }}">Portfolio</a></li>
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Services</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('services') }}#service-web-development">Web Development</a></li>
                    <li><a href="{{ route('services') }}#service-mobile-apps">Mobile App Development</a></li>
                    <li><a href="{{ route('services') }}#service-ai-automation">AI &amp; Automation</a></li>
                    <li><a href="{{ route('services') }}#service-ui-ux-design">UI/UX Design</a></li>
                    <li><a href="{{ route('services') }}#service-cloud-devops">Cloud &amp; DevOps</a></li>
                    <li><a href="{{ route('services') }}#service-maintenance-support">Maintenance &amp; Support</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2" id="footer-contact">
                <h6>Contact Info</h6>
                <ul class="footer-links footer-contact-list">
                    <li><i class="bi bi-geo-alt"></i> House-23, Road-5, Dhanmondi, Dhaka-1205, Bangladesh</li>
                    <li><i class="bi bi-telephone"></i> <a href="tel:+8801234567890">+880 1234-567890</a></li>
                    <li><i class="bi bi-envelope"></i> <a href="mailto:info@codexehub.com">info@codexehub.com</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h6>Follow Us</h6>
                <ul class="footer-links">
                    <li><a href="https://linkedin.com" target="_blank" rel="noopener"><i class="bi bi-linkedin me-2"></i>LinkedIn</a></li>
                    <li><a href="https://facebook.com" target="_blank" rel="noopener"><i class="bi bi-facebook me-2"></i>Facebook</a></li>
                    <li><a href="https://x.com" target="_blank" rel="noopener"><i class="bi bi-twitter-x me-2"></i>Twitter</a></li>
                    <li><a href="https://instagram.com" target="_blank" rel="noopener"><i class="bi bi-instagram me-2"></i>Instagram</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <p class="mb-0">&copy; {{ date('Y') }} CodexeHub. All rights reserved.</p>
            <div class="footer-legal">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms &amp; Conditions</a>
            </div>
        </div>
    </div>
</footer>
