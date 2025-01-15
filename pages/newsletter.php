<?php
session_start();
require_once "../database/db_connect.php";

// Include header si navbar
include "../includes/header.php";
include "../includes/navbar.php";

// Variabile de stare
$isSubscribed = false;
$message = "";

// Verifica dacă utilizatorul este logat
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    // Verifica dacă utilizatorul este deja abonat
    $stmt = $conn->prepare("SELECT * FROM newsletter_subscribers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $isSubscribed = $res->num_rows > 0;

    // Gestioneaza subscrierea/dezabonarea
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['subscribe'])) {
            if (!$isSubscribed) {
                // Adauga utilizatorul în tabel
                $insertStmt = $conn->prepare("INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW())");
                $insertStmt->bind_param("s", $email);
                if ($insertStmt->execute()) {
                    $isSubscribed = true;
                    $message = "You have successfully subscribed to the newsletter.";
                }
            } else {
                $message = "You are already subscribed.";
            }
        } elseif (isset($_POST['unsubscribe'])) {
            if ($isSubscribed) {
                // Sterge utilizatorul din tabel
                $deleteStmt = $conn->prepare("DELETE FROM newsletter_subscribers WHERE email = ?");
                $deleteStmt->bind_param("s", $email);
                if ($deleteStmt->execute()) {
                    $isSubscribed = false;
                    $message = "You have successfully unsubscribed from the newsletter.";
                }
            } else {
                $message = "You are not subscribed.";
            }
        }
    }
}
?>

<!-- Wrapper pentru background -->
<div class="page-wrapper newsletter-background">
    <!-- Formular Newsletter -->
    <div class="page-content-container">
        <h2 class="mb-4">Newsletter Subscription</h2>
        <p>Subscribe to our newsletter to stay updated with the latest news and exclusive content.</p>

        <?php if (!isset($_SESSION['email'])): ?>
            <!-- Mesaj pentru utilizatori care nu sunt logați -->
            <div class="alert alert-warning">
                You must <a href="login" class="alert-link">log in</a> or <a href="register" class="alert-link">register</a> to subscribe to the newsletter.
            </div>
        <?php else: ?>
            <!-- Mesaj de stare -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Formulare pentru subscriere/dezabonare -->
            <?php if ($isSubscribed): ?>
                <div class="alert alert-success">
                    You are already subscribed to the newsletter. Thank you for staying connected!
                </div>
                <form method="POST" action="">
                    <button type="submit" name="unsubscribe" class="btn btn-danger w-100">Unsubscribe</button>
                </form>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Your Email Address:</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly />
                    </div>
                    <button type="submit" name="subscribe" class="btn btn-success w-100">Subscribe</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
