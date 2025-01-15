<!-- Acest script nu este folosit -->
<?php
session_start();

// Incarca cofiguratia
require_once "../database/db_connect.php";
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Mapeaza valori din configuratie in variabile
$config = require __DIR__ . '/../config/config.php';
$smtpHost = $config['smtp_host'];
$smtpPort = $config['smtp_port'];
$smtpEncryption = $config['smtp_encryption'];
$smtpUsername = $config['smtp_username'];
$smtpPassword = $config['smtp_password'];
$fromEmail = $config['from_email'];
$fromName = $config['from_name'];

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Verifica daca userul exista si nu este verificat
$stmt = $conn->prepare("SELECT id, verification_token, verified FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
    $user = $res->fetch_assoc();
    if ($user['verified']) {
        $_SESSION['message'] = "Your email is already verified.";
        header("Location: dashboard.php");
        exit();
    }

    $verificationToken = $user['verification_token'];

    // Retrimite email de verificare
    $mail = new PHPMailer(true);
    try {
        // Setari SMTP
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = $smtpEncryption;
        $mail->Port = $smtpPort;

        // Trimitator si destinatar
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($email);

        // Continut Email
        $verifyLink = "https://echonewsmagazine.iceiy.com/pages/verify.php?token=$verificationToken";
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email';
        $mail->Body = "Thank you for registering.<br>Please verify your email by clicking the link below:<br><a href='$verifyLink'>$verifyLink</a>";

        $mail->send();
        $_SESSION['message'] = "Verification email has been sent successfully.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Verification email could not be sent. Error: " . $mail->ErrorInfo;
    }
} else {
    $_SESSION['message'] = "User not found.";
}

header("Location: dashboard.php");
exit();
?>
