<!-- Acest script nu este folosit -->
<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

include "../includes/header_simple.php";
?>

<div class="page-wrapper">
    <div class="page-content-container">
        <h2>Settings</h2>

        <!-- Selector de limba -->
        <h4>Language</h4>
        <select class="form-select mb-3">
            <option value="en" selected>English</option>
            <option value="es">Spanish</option>
            <option value="fr">French</option>
        </select>

        <!-- Comutator tematica -->
        <h4>Theme</h4>
        <button class="btn btn-secondary mb-3" onclick="toggleTheme()">Toggle Dark Mode</button>

        <!-- Stergere cont -->
        <h4>Delete Account</h4>
        <form method="POST" action="delete_account.php">
            <div class="mb-3">
                <label for="password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="g-recaptcha mb-3" data-sitekey="YOUR_RECAPTCHA_SITE_KEY"></div>
            <button type="submit" class="btn btn-danger">Delete Account</button>
        </form>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
function toggleTheme() {
    document.body.classList.toggle('dark-mode');
}
</script>

<?php include "../includes/footer.php"; ?>
