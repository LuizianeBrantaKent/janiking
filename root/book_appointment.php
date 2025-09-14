<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // echo "<pre>"; print_r($_POST); echo "</pre>"; // Debug: Comment out or remove
    include '../db/config.php';

    $firstName = htmlspecialchars(trim($_POST['firstName']));
    $lastName = htmlspecialchars(trim($_POST['lastName']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $preferredLocation = htmlspecialchars(trim($_POST['preferredLocation']));
    $additionalInfo = htmlspecialchars(trim($_POST['additionalInfo']));
    $appointmentDate = trim($_POST['appointmentDate'] ?? '');
    $appointmentTime = trim($_POST['appointmentTime'] ?? '');

    $errors = [];

    if (
        empty($firstName) || empty($lastName) ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        empty($phone) || empty($preferredLocation) ||
        empty($appointmentDate) || empty($appointmentTime)
    ) {
        $errors[] = "All fields are required.";
    }

    if (empty($errors)) {
        $scheduledDate = $appointmentDate . ' ' . $appointmentTime;
        $endDate = date('Y-m-d H:i:s', strtotime($scheduledDate) + 3600); // 1 hour window

        $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE scheduled_date BETWEEN ? AND ?");
        $stmt->execute([$scheduledDate, $endDate]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $errors[] = "This time slot is already booked. Please choose another.";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO bookings 
                (franchisee_id, first_name, last_name, email, phone, preferred_location, scheduled_date, status, notes) 
                VALUES (NULL, ?, ?, ?, ?, ?, ?, 'Pending', ?)
            ");
            $stmt->execute([$firstName, $lastName, $email, $phone, $preferredLocation, $scheduledDate, $additionalInfo]);

            echo '<div class="alert alert-success">Appointment confirmed! We will contact you soon.</div>';
        }
    }

    if (!empty($errors)) {
        echo '<div class="alert alert-danger">';
        foreach ($errors as $error) {
            echo '<p>' . $error . '</p>';
        }
        echo '</div>';
    }
}
?>

<?php include 'includes/guest_header.php'; ?>
<?php include 'includes/guest_navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section text-center book-appointment-hero">
    <div class="container">
        <h1 class="display-4 font-weight-bold mb-4">Schedule Your Franchise Consultation</h1>
        <p class="lead mb-5">Take the first step toward owning your JaniKing commercial cleaning franchise.</p>
    </div>
</section>

<!-- Appointment Booking Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-5">
                <div class="why-book mb-4">
                    <h3 class="font-weight-bold mb-4">Why Book a Consultation?</h3>
                    <div class="feature-box bg-white mb-3">
                        <i class="fas fa-user-check feature-icon"></i>
                        <h4>Personalized Guidance</h4>
                        <p>Get answers to your specific questions about the JaniKing franchise opportunity.</p>
                    </div>
                    <div class="feature-box bg-white mb-3">
                        <i class="fas fa-dollar-sign feature-icon"></i>
                        <h4>Investment Details</h4>
                        <p>Learn about startup costs, ongoing fees, and potential return on investment.</p>
                    </div>
                    <div class="feature-box bg-white mb-3">
                        <i class="fas fa-map-marked-alt feature-icon"></i>
                        <h4>Territory Analysis</h4>
                        <p>Discover available territories and market potential in your desired location.</p>
                    </div>
                    <div class="feature-box bg-white mb-3">
                        <i class="fas fa-graduation-cap feature-icon"></i>
                        <h4>Training & Support</h4>
                        <p>Understand our comprehensive training program and ongoing support systems.</p>
                    </div>
                </div>
                <div class="quote-section bg-white p-4 border rounded text-center">
                    <img src="/assets/images/consultant.jpg" alt="Franchise Advisor" class="img-fluid rounded-circle mb-3">
                    <p class="font-italic">"Our franchise advisors are dedicated to helping you make an informed decision about your business future."</p>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-7">
                <form method="POST" action="">
                    <h3 class="font-weight-bold mb-4">Select Appointment Date & Time</h3>
                    <div class="calendar-section mb-4">
                        <input type="text" class="form-control" id="appointmentDate" name="appointmentDate" placeholder="Select Date" required>
                        <div id="timeSlots" class="mt-3"></div>
                    </div>

                    <input type="hidden" id="appointmentTime" name="appointmentTime"> <!-- Hidden input for time -->

                    <h3 class="font-weight-bold mb-4">Your Information</h3>
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
                        <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="+61 4XX XXX XXX" required>
                    </div>
                    <div class="form-group">
                        <label for="preferredLocation">Preferred Location</label>
                        <input type="text" class="form-control" id="preferredLocation" name="preferredLocation" required>
                    </div>
                    <div class="form-group">
                        <label for="additionalInfo">Additional Information (Optional)</label>
                        <textarea class="form-control" id="additionalInfo" name="additionalInfo" rows="3"></textarea>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">I agree to the <a href="#" data-toggle="modal" data-target="#termsModal">Terms of Service</a> and <a href="#" data-toggle="modal" data-target="#privacyModal">Privacy Policy</a></label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Confirm Appointment</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Modals (Terms & Privacy same as before) -->
 <!-- Terms of Service Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="termsModalLabel">Terms of Service - JaniKing Appointment Booking</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>By booking an appointment with JaniKing through this portal, you agree to the following Terms & Conditions:</p>
        <ul>
          <li>Appointments are considered requests until confirmed by JaniKing.</li>
          <li>You will receive confirmation by email or phone once approved.</li>
          <li>You may reschedule/cancel up to 24 hours before your appointment.</li>
          <li>We reserve the right to update these terms at any time.</li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Privacy Policy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1" role="dialog" aria-labelledby="privacyModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="privacyModalLabel">Privacy Policy - JaniKing Appointment Booking</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Your privacy is important to us. We only use your information for booking and service delivery. We do not sell your data.</p>
        <ul>
          <li>We collect your contact and booking details to process your appointment.</li>
          <li>Data is securely stored and only shared with authorized staff.</li>
          <li>You may request to update or delete your data anytime.</li>
          <li>This policy may be updated and will be posted here.</li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/guest_footer.php'; ?>
<script src="/assets/js/book_appointment.js"></script>