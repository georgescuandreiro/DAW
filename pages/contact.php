<?php
session_start();
require_once "../database/db_connect.php";

// Incarca configuratia (credentiale)
$config = require __DIR__ . '/../config/config.php';

// Include header si navbar
include "../includes/header.php";
include "../includes/navbar.php";

// Verifica daca userul este logat
$isLoggedIn = isset($_SESSION['email']);
?>

<!-- Wrapper pentru background -->
<div class="page-wrapper contact-background">
    <!-- Container formular de Contact -->
    <div class="page-content-container">
        <h2 class="mb-4">Contact Us</h2>
        <p>If you have any questions, feedback, or concerns, feel free to reach out to us using the form below.</p>

        <?php if (!$isLoggedIn): ?>
            <!-- Mesaj pentru useri neautentificati -->
            <div class="alert alert-warning">
                You must <a href="login" class="alert-link">log in</a> or <a href="register" class="alert-link">register</a> to use the contact form.
            </div>
        <?php else: ?>
            <!-- Forumalr de contact pentru user autentificati -->
            <form method="POST" action="contact_process">
                <div class="mb-3">
                    <label for="name" class="form-label">Your Name:</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Your Email Address:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Your Message:</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                
                <!-- reCAPTCHA v2 -->
                <div class="recaptcha-container">
                    <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($config['recaptcha_site_key']); ?>"></div>
                </div>
                
                <button type="submit" class="btn btn-success w-100">Send Message</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- reCAPTCHA API script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php include "../includes/footer.php"; ?>
