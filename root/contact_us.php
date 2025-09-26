<?php include 'includes/guest_header.php'; ?>
<?php include 'includes/guest_navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section text-center contact-us-hero">
    <div class="container">
        <h1 class="display-4 font-weight-bold mb-4">Contact Us</h1>
    </div>
</section>

<!-- Contact Section -->
<section class="section-padding">
    <div class="container">
        <div class="row align-items-stretch">
            <!-- Send Us a Message Form -->
            <div class="col-md-6 mb-4">
                <div class="contact-form p-4 bg-white border rounded">
                    <h3 class="font-weight-bold mb-4">Send Us a Message</h3>
                    <form action="/submit_contact.php" method="POST" id="contactForm">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required pattern="^\+?61\s?(4\d{2}|0[2-8])\s?\d{3}\s?\d{3}$" placeholder="+61 4XX XXX XXX or (02) XXXX XXXX">
                        </div>
                        <div class="form-group">
                            <label for="interest">I'm Interested In</label>
                            <select class="form-control" id="interest" name="interest" required>
                                <option value="">Select an option</option>
                                <option value="franchise">Franchise Opportunities</option>
                                <option value="services">Cleaning Services</option>
                                <option value="support">Support & Training</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="consent" name="consent" required>
                            <label class="form-check-label" for="consent">I consent to JaniKing collecting my data and contacting me.</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">Submit Inquiry</button>
                    </form>
                </div>
            </div>

            <!-- Our Locations and Contact Information -->
            <div class="col-md-6 mb-4">
                <div class="contact-info p-4 bg-white border rounded h-100 d-flex flex-column">
                    <!-- Our Locations -->
                    <div class="mb-4">
                        <h3 class="font-weight-bold mb-3">Our Locations</h3>
                        <!-- Map Section -->
                        <section class="section-padding" style="background-color: var(--light-bg);">
                            <div class="container">                               
                                <div class="row">
                                    <div class="col-12">
                                        <div id="map" style="height: 300px;"></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Load Leaflet.js -->
                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <h3 class="font-weight-bold mb-3">Contact Information</h3>
                        <p><i class="fas fa-map-marker-alt"></i> 123 Cleaning Lane, Suite 100, New York, NY 10001</p>
                        <p><i class="fas fa-phone"></i> +1-800-555-0123</p>
                        <p><i class="fas fa-envelope"></i> info@janiking.com</p>
                        <p><i class="fas fa-clock"></i> Mon-Fri: 9:00 AM - 5:00 PM EST</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Need Immediate Assistance -->
<section class="section-padding bg-light">
    <div class="container text-center">
        <h2 class="font-weight-bold mb-4">Need Immediate Assistance?</h2>
        <p class="lead mb-4">Get in touch with us right away.</p>
        <div class="row justify-content-center">
            <div class="col-md-4 mb-3">
                <button class="btn btn-primary btn-lg btn-block" id="startLiveChat">Start Live Chat</button>
            </div>
            <div class="col-md-4 mb-3">
                <a href="/index.php" class="btn btn-outline-primary btn-lg btn-block">Back to Home</a>
            </div>
        </div>
    </div>
</section>

<!-- Support Boxes -->
<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4 mb-4">
                <div class="support-box p-4 bg-white border rounded text-center">
                    <h4 class="font-weight-bold mb-3">Franchise Support</h4>
                    <p>Expert guidance for your franchise journey.</p>
                    <button class="btn btn-link learn-more" data-target="franchise-support">Learn More</button>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="support-box p-4 bg-white border rounded text-center">
                    <h4 class="font-weight-bold mb-3">Training Programs</h4>
                    <p>Comprehensive training for franchise success.</p>
                    <button class="btn btn-link learn-more" data-target="training-programs">Learn More</button>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="support-box p-4 bg-white border rounded text-center">
                    <h4 class="font-weight-bold mb-3">Franchise FAQ</h4>
                    <p>Answers to your most common questions.</p>
                    <button class="btn btn-link learn-more" data-target="franchise-faq">Learn More</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Learn More Modal -->
<div class="modal fade" id="learnMoreModal" tabindex="-1" role="dialog" aria-labelledby="learnMoreModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="learnMoreModalLabel">More Information</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="learnMoreContent">
        <!-- Dynamic content goes here -->
      </div>
    </div>
  </div>
</div>

<!-- Link Custom JS -->
<script src="/assets/js/contact_us.js"></script>

<?php include 'includes/guest_footer.php'; ?>
