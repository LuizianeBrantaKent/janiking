<?php include 'includes/guest_header.php'; ?>
<?php include 'includes/guest_navbar.php'; ?>

<!-- Contact Submission Section -->
<section class="section-padding">
    <div class="container">
        <div class="row align-items-stretch">
            <!-- Left Column: Hero with Text -->
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="hero-left">
                    <div>
                        <h2 class="font-weight-bold mb-4">Contact Us</h2>
                        <p class="lead mb-4">Thank you for reaching out. We’ll get back to you soon!</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Contact Form Response -->
            <div class="col-md-6">
                <div class="contact-form p-4 bg-white border rounded">
                    <h3 class="font-weight-bold mb-4">Contact Submission</h3>
                    <?php
                    session_start();
                    include '../db/config.php';
                    require 'phpmailer/src/PHPMailer.php';
                    require 'phpmailer/src/SMTP.php';
                    require 'phpmailer/src/Exception.php';
                    use PHPMailer\PHPMailer\PHPMailer;
                    use PHPMailer\PHPMailer\Exception;

                    $success = '';
                    $errors = [];

                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
                        $lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
                        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
                        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
                        $interest = htmlspecialchars(trim($_POST['interest'] ?? ''));
                        $message = htmlspecialchars(trim($_POST['message'] ?? ''));
                        $consent = isset($_POST['consent']) ? 'Yes' : 'No';

                        if (empty($firstName)) {
                            $errors[] = "First Name is required.";
                        }
                        if (empty($lastName)) {
                            $errors[] = "Last Name is required.";
                        }
                        if (empty($email)) {
                            $errors[] = "Email is required.";
                        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = "Invalid email format.";
                        }
                        if (empty($phone)) {
                            $errors[] = "Phone Number is required.";
                        }
                        if (empty($interest)) {
                            $errors[] = "Interest is required.";
                        }
                        if (empty($message)) {
                            $errors[] = "Message is required.";
                        }
                        if ($consent !== 'Yes') {
                            $errors[] = "You must consent to data collection.";
                        }

                        if (empty($errors)) {
                            try {
                                // Insert into database
                                $stmt = $conn->prepare("INSERT INTO contact_inquiries (first_name, last_name, email, phone, interest, message, consent) VALUES (?, ?, ?, ?, ?, ?, ?)");
                                $stmt->execute([$firstName, $lastName, $email, $phone, $interest, $message, $consent]);

                                // Send email to admin/staff
                                $mail = new PHPMailer(true);
                                $mail->isSMTP();
                                $mail->Host = 'smtp.gmail.com';
                                $mail->SMTPAuth = true;
                                $mail->Username = 'tsv.thaisvieira@gmail.com'; // Replace with your Gmail
                                $mail->Password = 'qtod jgmb kszo emse'; // Replace with your app password
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                                $mail->Port = 465;

                                $mail->setFrom($email, "$firstName $lastName");
                                $mail->addAddress('tsv.thaisvieira@gmail.com'); // Replace with admin email or same for testing
                                $mail->Subject = 'New Contact Inquiry';
                                $mail->Body = "New contact inquiry:\n\n"
                                            . "Name: $firstName $lastName\n"
                                            . "Email: $email\n"
                                            . "Phone: $phone\n"
                                            . "Interest: $interest\n"
                                            . "Message: $message\n"
                                            . "Consent: $consent";

                                $mail->send();

                                $success = "Your email has been sent and we will contact you soon.";
                            } catch (Exception $e) {
                                $errors[] = "Failed to send email. Please try again later. Error: " . $e->getMessage();
                            }
                        }
                    }

                    if (!empty($errors)) {
                        echo '<div class="alert alert-danger">';
                        foreach ($errors as $error) {
                            echo '<p>' . $error . '</p>';
                        }
                        echo '</div>';
                    }
                    if ($success) {
                        echo '<div class="alert alert-success">' . $success . '</div>';
                    } else {
                        if (!empty($errors)) {
                            header('Location: /contact_us.php?error=' . urlencode(implode('|', $errors)));
                            exit;
                        }
                    }
                    ?>
                    <p class="text-center mt-3">
                        <a href="/contact_us.php" class="btn btn-primary btn-lg">Back to Contact Us</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- No JS needed for now -->
<?php include 'includes/guest_footer.php'; ?>