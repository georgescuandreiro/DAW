<?php
session_start();
require_once "../database/db_connect.php";

// Redirectioneaza useri nelogati catre login
if (!isset($_SESSION['email'])) {
    $_SESSION['message'] = "You must log in to subscribe to the newsletter.";
    header("Location: login");
    exit();
}

// Proceseaza subscriptia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['email'];

    // Verifica daca este deja subscribed
    $stmt = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $_SESSION['message'] = "You are already subscribed to the newsletter.";
    } else {
        // Adaugare subscriptie
        $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Thank you for subscribing to our newsletter!";
        } else {
            $_SESSION['message'] = "An error occurred. Please try again later.";
        }
    }

    header("Location: newsletter.php");
    exit();
}
?>
