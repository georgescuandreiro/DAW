<?php
require_once "../database/db_connect.php";

// Obține token-ul de verificare din URL, dacă există
$token = $_GET['token'] ?? '';

// Setează un mesaj implicit care indică un token invalid sau expirat
$message = "Invalid or expired verification token.";

// Verifică dacă token-ul a fost furnizat
if ($token) {
    // Pregătește o interogare pentru a găsi utilizatorul cu acest token
    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    // Verifică dacă a fost găsit un utilizator cu token-ul furnizat
    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $userId = $row['id'];

        // Pregătește o interogare pentru a marca utilizatorul ca verificat
        $update = $conn->prepare("UPDATE users SET verified = 1, verification_token = NULL WHERE id = ?");
        $update->bind_param("i", $userId);
        // Execută actualizarea și verifică dacă a fost realizată cu succes
        if ($update->execute()) {
            $message = "Your email has been verified. Thank you!";
        } else {
            $message = "Error verifying your account.";
        }
    }
}

// Afiseaza un mesaj simplu
?>
<?php include "../includes/header.php"; ?>
<div class="container mt-5">
    <h2>Email Verification</h2>
    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <p><a href="login.php">Go to Login</a></p>
</div>
<?php include "../includes/footer.php"; ?>
