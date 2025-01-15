<?php
session_start();
require_once "../database/db_connect.php";

// Include configuratia (credentiale)
$config = require __DIR__ . '/../config/config.php';

// Chei reCAPTCHA
$recaptchaSiteKey = $config['recaptcha_site_key'];
$recaptchaSecretKey = $config['recaptcha_secret_key'];

// Redirectioneaza catre dashboard daca userul este deja logat 
if (isset($_SESSION['email'])) {
    header("Location: dashboard");
    exit();
}

// Initializare mesaj eroare si contor pentru incercari de logare
$error = "";
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0; // Initializare contor pentru incercari esuate de logare
}

// Controleaza requesturile POST pentru login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    // Verifica daca reCAPTCHA este necesar ( apare doar dupa 3 incercari de logare esuate )
    $recaptchaRequired = $_SESSION['failed_attempts'] >= 3;

    if ($recaptchaRequired) {
        // Validare reCAPTCHA
        if (!empty($recaptchaResponse)) {
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
        } else {
            $error = "Please complete the reCAPTCHA.";
        }
    }

    // Daca nu exista eroare, verifica credentialele
    if (empty($error)) {
        $stmt = $conn->prepare("SELECT id, password_hash, is_admin FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 1) {
                $row = $res->fetch_assoc();
                if (password_verify($password, $row['password_hash'])) {
                    // Logare cu success
                    $_SESSION['user_id'] = $row['id']; // Seteaza ID sesiune
                    $_SESSION['email'] = $email; // Seteaza emailul
                    $_SESSION['is_admin'] = $row['is_admin']; // Seteaza flagul de admin (is_admin)
                    $_SESSION['failed_attempts'] = 0; // Reseteaza incercari esuate de login (contorul)
                    header("Location: dashboard");
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Database error occurred.";
        }
    }

    // Incremente pentru incercari de logare esuate
    if (!empty($error)) {
        $_SESSION['failed_attempts']++;
    }
}

include "../includes/header_simple.php";
?>
<!-- Cod HTML pentru formular -->
<div class="login-wrapper">
    <div class="login-container">
        <h2 class="text-center mb-4">Login</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="login">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <!-- Arata reCAPTCHA conditionat de contor -->
            <?php if ($_SESSION['failed_attempts'] >= 3): ?>
                <div class="recaptcha-container mb-3">
                    <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptchaSiteKey); ?>"></div>
                </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-success w-100">Login</button>
        </form>
        <p class="text-center mt-3">Don't have an account? <a href="register">Register here</a>.</p>
        <p class="text-center">Forgot your password? <a href="reset_password">Reset it here</a>.</p>
    </div>
</div>
<?php if ($_SESSION['failed_attempts'] >= 3): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
