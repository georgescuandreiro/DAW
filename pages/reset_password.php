<?php
session_start();
require_once "../database/db_connect.php";

// Include libraria PHPMailer
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include fisierul de configuratie (credentiale)
$config = require __DIR__ . '/../config/config.php';

// Map configuration values
$smtpHost = $config['smtp_host'];
$smtpPort = $config['smtp_port'];
$smtpEncryption = $config['smtp_encryption'];
$smtpUsername = $config['smtp_username'];
$smtpPassword = $config['smtp_password'];
$fromEmail = $config['from_email'];
$fromName = $config['from_name'];
$recaptchaSiteKey = $config['recaptcha_site_key'];
$recaptchaSecretKey = $config['recaptcha_secret_key'];

// Initializeaza variabile
$error = "";
$success = "";
$token = isset($_GET['token']) ? $_GET['token'] : "";
$pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>])[A-Za-z\d!@#$%^&*(),.?":{}|<>]{8,}$/';

// Controleaza formularul de trimitere
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (isset($_POST['email']) && empty($token)) {
        $email = trim($_POST['email']);

        if (empty($recaptchaResponse)) {
            $error = "Please complete the reCAPTCHA.";
        } else {
            // Validare reCAPTCHA
            $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
            $data = ['secret' => $recaptchaSecretKey, 'response' => $recaptchaResponse, 'remoteip' => $_SERVER['REMOTE_ADDR']];

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

        // Proceseaza emailul si trimite link-ul de reset
        if (empty($error)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 1) {
                $resetToken = bin2hex(random_bytes(16));

                $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $del->bind_param("s", $email);
                $del->execute();

                $ins = $conn->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
                $ins->bind_param("ss", $email, $resetToken);

                if ($ins->execute()) {
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = $smtpHost;
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtpUsername;
                        $mail->Password = $smtpPassword;
                        $mail->SMTPSecure = $smtpEncryption;
                        $mail->Port = $smtpPort;

                        $mail->setFrom($fromEmail, $fromName);
                        $mail->addAddress($email);

                        $resetLink = "https://echonewsmagazine.iceiy.com/pages/reset_password.php?token=$resetToken";
                        $mail->isHTML(true);
                        $mail->Subject = 'Reset Your Password';
                        $mail->Body = "Click the link below to reset your password:<br><a href='$resetLink'>$resetLink</a>";

                        $mail->send();
                        $success = "A password reset link has been sent to your email.";
                    } catch (Exception $e) {
                        $error = "Failed to send the reset email. Please try again.";
                    }
                } else {
                    $error = "Error generating reset token.";
                }
            } else {
                $error = "No account found with that email.";
            }
        }
    } elseif (!empty($token) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        // Proceseaza resetarea parolei
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (!preg_match($pattern, $new_password)) {
            $error = "Password must have at least one lowercase letter, one uppercase letter, one digit, one special character, and be at least 8 characters long.";
        } else {
            $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 1) {
                $email = $res->fetch_assoc()['email'];
                $passwordHash = password_hash($new_password, PASSWORD_BCRYPT);

                $upd = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $upd->bind_param("ss", $passwordHash, $email);

                if ($upd->execute()) {
                    $del = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                    $del->bind_param("s", $token);
                    $del->execute();

                    $success = "Password updated successfully.";
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Error updating your password.";
                }
            } else {
                $error = "Invalid or expired token.";
            }
        }
    }
}

include "../includes/header_simple.php";
?>

<div class="login-wrapper">
    <!-- Formular parola noua  -->
    <div class="login-container">
        <h2 class="text-center mb-4">Reset Password</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (empty($token)): ?>
            <!-- Cerere link de resetare -->
            <form method="POST" action="reset_password.php">
                <div class="mb-3">
                    <label>Your Email Address:</label>
                    <input type="email" name="email" class="form-control" required />
                </div>
                <div class="recaptcha-container mb-3">
                    <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptchaSiteKey); ?>"></div>
                </div>
                <button type="submit" class="btn btn-success w-100">Send Reset Link</button>
            </form>
        <?php else: ?>
            <!-- Resetare parola -->
            <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">
                <div class="mb-3">
                    <label>New Password:</label>
                    <input type="password" name="new_password" class="form-control" required />
                </div>
                <div class="mb-3">
                    <label>Confirm New Password:</label>
                    <input type="password" name="confirm_password" class="form-control" required />
                </div>
                <button type="submit" class="btn btn-success w-100">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Script reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>