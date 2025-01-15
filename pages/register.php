<?php
session_start();
require_once "../database/db_connect.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include configuratia (credentiale)
$config = require __DIR__ . '/../config/config.php';

// Chei reCAPTCHA
$recaptchaSiteKey = $config['recaptcha_site_key'];
$recaptchaSecretKey = $config['recaptcha_secret_key'];

// Include PHPMailer pentru verificare email
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Redirectioneaza daca userul este deja logat
if (isset($_SESSION['email'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

// Controleaza formularul de registrare
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>])[A-Za-z\d!@#$%^&*(),.?":{}|<>]{8,}$/';

    // Validare reCAPTCHA
    if (empty($recaptchaResponse)) {
        $error = "Please complete the reCAPTCHA.";
    } else {
        $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $recaptchaSecretKey,
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verifyURL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $responseKeys = json_decode($response, true);
        if (!$responseKeys['success']) {
            $error = "reCAPTCHA verification failed. Please try again.";
        }
    }

    if (empty($error)) {
        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (!preg_match($pattern, $password)) {
            $error = "Password must include lowercase, uppercase, digit, special character, and be at least 8 characters.";
        } else {
            // Verifica daca emailul exista deja in baza de date 
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $checkRes = $check->get_result();
            if ($checkRes->num_rows > 0) {
                $error = "Email already registered.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                // Insereaza user
                $stmt = $conn->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
                $stmt->bind_param("ss", $email, $passwordHash);
                if ($stmt->execute()) {
                    $verificationToken = bin2hex(random_bytes(16));
                    $upd = $conn->prepare("UPDATE users SET verification_token = ? WHERE email = ?");
                    $upd->bind_param("ss", $verificationToken, $email);
                    $upd->execute();

                    // Trimite email de verificare
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = $config['smtp_host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $config['smtp_username'];
                        $mail->Password = $config['smtp_password'];
                        $mail->SMTPSecure = $config['smtp_encryption'];
                        $mail->Port = $config['smtp_port'];

                        $mail->setFrom($config['from_email'], $config['from_name']);
                        $mail->addAddress($email);

                        $verifyLink = "https://echonewsmagazine.iceiy.com/pages/verify.php?token=$verificationToken";
                        $mail->isHTML(true);
                        $mail->Subject = 'Verify Your Email';
                        $mail->Body = "Please verify your email by clicking this link: <a href='$verifyLink'>$verifyLink</a>";

                        $mail->send();
                    } catch (Exception $e) {
                        $error = "Error sending verification email.";
                    }

                    $_SESSION['email'] = $email;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Error registering user.";
                }
            }
        }
    }
}

include "../includes/header_simple.php";
?>

<!-- Wrapper pentru background -->
<div class="login-wrapper">
    
    <!-- Formular de registrare -->
    <div class="login-container">
        <h2 class="text-center mb-4">Register</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="register.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <!-- Casuta reCAPTCHA -->
            <div class="recaptcha-container mb-3">
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptchaSiteKey); ?>"></div>
            </div>
            <button type="submit" class="btn btn-success w-100">Register</button>
        </form>
        <p class="text-center mt-3">Already have an account? <a href="login">Login here</a>.</p>
    </div>
</div>

<!-- reCAPTCHA API script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
