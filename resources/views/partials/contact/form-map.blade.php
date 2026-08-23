<section class="section" id="contact-form">
    <div class="container-xxl">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-6">
                <div class="contact-form-card" data-aos="fade-up">
                    <h2>Send Us a Message</h2>
                    <p>Fill out the form below and our team will get back to you as soon as possible.</p>

                    <div class="alert alert-success d-none" role="alert" data-contact-success></div>
                    <div class="alert alert-danger d-none" role="alert" data-contact-error>Something went wrong. Please try again.</div>

                    <form action="{{ route('contact.store') }}" method="POST" class="contact-form" novalidate data-contact-form>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="contact-name" class="visually-hidden">Your Name</label>
                                <div class="input-icon-group">
                                    <i class="bi bi-person"></i>
                                    <input type="text" class="form-control" id="contact-name" name="name" placeholder="Your Name *" required>
                                </div>
                                <div class="invalid-feedback d-block" data-contact-field-error="name"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="contact-email" class="visually-hidden">Your Email</label>
                                <div class="input-icon-group">
                                    <i class="bi bi-envelope"></i>
                                    <input type="email" class="form-control" id="contact-email" name="email" placeholder="Your Email *" required>
                                </div>
                                <div class="invalid-feedback d-block" data-contact-field-error="email"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="contact-phone" class="visually-hidden">Phone Number</label>
                                <div class="input-icon-group">
                                    <i class="bi bi-telephone"></i>
                                    <input type="tel" class="form-control" id="contact-phone" name="phone" placeholder="Phone Number">
                                </div>
                                <div class="invalid-feedback d-block" data-contact-field-error="phone"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="contact-subject" class="visually-hidden">Subject</label>
                                <div class="input-icon-group">
                                    <i class="bi bi-tag"></i>
                                    <input type="text" class="form-control" id="contact-subject" name="subject" placeholder="Subject">
                                </div>
                                <div class="invalid-feedback d-block" data-contact-field-error="subject"></div>
                            </div>
                            <div class="col-12">
                                <label for="contact-message" class="visually-hidden">Your Message</label>
                                <div class="input-icon-group input-icon-group-textarea">
                                    <i class="bi bi-pencil"></i>
                                    <textarea class="form-control" id="contact-message" name="message" rows="5" placeholder="Your Message *" required></textarea>
                                </div>
                                <div class="invalid-feedback d-block" data-contact-field-error="message"></div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="contact-privacy" name="privacy" required>
                                    <label class="form-check-label" for="contact-privacy">I agree to the <a href="#">Privacy Policy</a></label>
                                </div>
                                <div class="invalid-feedback d-block" data-contact-field-error="privacy"></div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg" data-contact-submit>
                                    <span data-contact-submit-text>Send Message <i class="bi bi-send"></i></span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" data-contact-spinner></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="contact-map-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="map-address-card">
                        <strong>CodexeHub</strong>
                        <p>House-23, Road-5, Dhanmondi<br>Dhaka-1205, Bangladesh</p>
                    </div>
                    <iframe src="https://www.google.com/maps?q=Dhanmondi,Dhaka,Bangladesh&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="CodexeHub office location"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
