<?php
session_start();
require_once "../database/db_connect.php";

// Include librariile PHPMailer
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incarca configuratia ( credentiale )
$config = require __DIR__ . '/../config/config.php';

// Atribuie valorile de configuratie in variabile
$smtpHost = $config['smtp_host'];
$smtpPort = $config['smtp_port'];
$smtpEncryption = $config['smtp_encryption'];
$smtpUsername = $config['smtp_username'];
$smtpPassword = $config['smtp_password'];
$fromEmail = $config['from_email'];
$fromName = $config['from_name'];
$toEmail = $config['to_email'];
$recaptchaSecretKey = $config['recaptcha_secret_key']; // Cheia secreta reCAPTCHA

// Initializare mesaje de eroare si success
$error = "";
$success = "";

// Verifica daca utilizatorul este autentificat
if (!isset($_SESSION['email'])) {
    $_SESSION['contact_error'] = "You must be logged in to send a message.";
    header("Location: contact.php");
    exit();
}

// Formularul de trimitere
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    $recaptchaResponse = $_POST['g-recaptcha-response']; // Răspunsul reCAPTCHA

    // Verifică dacă utilizatorul încearcă să trimită email din alt cont
    if ($email !== $_SESSION['email']) {
        $_SESSION['contact_error'] = "You can only send messages using your registered email address.";
        header("Location: contact.php");
        exit();
    }

    // Validare reCAPTCHA
    if (empty($recaptchaResponse)) {
        $_SESSION['contact_error'] = "Please complete the reCAPTCHA.";
        header("Location: contact.php");
        exit();
    } else {
        // Verifică răspunsul reCAPTCHA prin API-ul Google
        $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $recaptchaSecretKey,
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        // Utilizează cURL pentru a verifica răspunsul reCAPTCHA
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verifyURL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $responseKeys = json_decode($response, true);

        if (!$responseKeys['success']) {
            $_SESSION['contact_error'] = "reCAPTCHA verification failed. Please try again.";
            header("Location: contact.php");
            exit();
        }
    }

    // Continuă cu validarea mesajului și trimiterea emailului
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['contact_error'] = "All fields are required.";
        header("Location: contact");
        exit();
    }

    // Salvează mesajul în baza de date
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);
    if (!$stmt->execute()) {
        $_SESSION['contact_error'] = "Failed to save your message. Please try again later.";
        header("Location: contact");
        exit();
    }

    // Trimite email către echipa de suport/contact
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUsername;
        $mail->Password   = $smtpPassword;
        $mail->SMTPSecure = $smtpEncryption;
        $mail->Port       = $smtpPort;

        // Setări pentru email
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, 'Support Team'); // Email destinat
        $mail->isHTML(true);
        $mail->Subject = "Contact Us Message from $name";
        $mail->Body    = "
            <strong>Name:</strong> $name<br>
            <strong>Email:</strong> $email<br>
            <strong>Message:</strong><br>" . nl2br($message);

        // Trimite emailul
        $mail->send();
        $_SESSION['contact_success'] = "Your message has been sent successfully. We'll get back to you shortly!";
    } catch (Exception $e) {
        $_SESSION['contact_error'] = "Failed to send your message. Please try again later.";
    }

    header("Location: contact");
    exit();
}
?>
