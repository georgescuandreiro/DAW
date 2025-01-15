<?php
session_start();
require_once "../database/db_connect.php";

// Script pentru trimitere newsletter (optional)
// Verifica daca userul este admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access denied.");
}

// Include libraria PHPMailer
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incarca configuratia
$config = require __DIR__ . '/../config/config.php';

// Mapeaza variabile
$smtpHost = $config['smtp_host'];
$smtpPort = $config['smtp_port'];
$smtpEncryption = $config['smtp_encryption'];
$smtpUsername = $config['smtp_username'];
$smtpPassword = $config['smtp_password'];
$fromEmail = $config['from_email'];
$fromName = $config['from_name'];

// Defineste continutul newsletter-ului
$newsletterSubject = "Our Latest Updates!";
$newsletterBody = "
    <h1>Welcome to Our Newsletter!</h1>
    <p>We are excited to share our latest updates with you. Stay tuned for more great content!</p>
    <p>Best regards,<br>The Team</p>
";

try {
    // Extrage toti subscriberii din baza
    $stmt = $conn->prepare("SELECT email FROM newsletter_subscribers");
    $stmt->execute();
    $result = $stmt->get_result();

    // Initializeaza libraria PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUsername;
    $mail->Password = $smtpPassword;
    $mail->SMTPSecure = $smtpEncryption;
    $mail->Port = $smtpPort;

    // Seteaza informatia trimitatorului
    $mail->setFrom($fromEmail, $fromName);

    // Trimite email subscriberilor
    while ($row = $result->fetch_assoc()) {
        $mail->clearAddresses();
        $mail->addAddress($row['email']);
        $mail->isHTML(true);
        $mail->Subject = $newsletterSubject;
        $mail->Body = $newsletterBody;

        try {
            $mail->send();
        } catch (Exception $e) {
            echo "Failed to send to {$row['email']}: {$mail->ErrorInfo}<br>";
        }
    }

    echo "Newsletter sent successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
