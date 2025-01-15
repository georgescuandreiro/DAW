<!-- BARA DE NAVIGATIE - NAVBAR -->

<?php
// Initializeaza sesiune daca nu este deja initializata
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initializeaza variabila de logare (email)
$isLoggedIn = isset($_SESSION['email']);
?>

<nav class="navbar custom-navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <!-- Linkuri din navbar -->
        <ul class="custom-navbar-links d-flex flex-wrap justify-content-center align-items-center mb-2 mb-lg-0">
            <li><a class="nav-link" href="/pages/dashboard">Home</a></li>
            <li><a class="nav-link" href="/pages/category1">World News & Politics</a></li>
            <li><a class="nav-link" href="/pages/category2">Science, Tech & Innovation</a></li>
            <li><a class="nav-link" href="/pages/category3">Lifestyle & Culture</a></li>
            <li><a class="nav-link" href="/pages/newsletter">Newsletter</a></li>
            <li><a class="nav-link" href="/pages/contact">Contact Us</a></li>

            <!-- Buton de profil (logat) / Butoane de Login/Register (nu este logat) -->
            <?php if ($isLoggedIn): ?>
                <li class="nav-item dropdown">
                    <a class="dropdown-toggle profile-btn" href="#" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- Pictograma profilului -->
                        <svg class="svg-inline--fa fa-circle-user" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="circle-user" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path fill="currentColor" d="M399 384.2C376.9 345.8 335.4 320 288 320l-64 0c-47.4 0-88.9 25.8-111 64.2c35.2 39.2 86.2 63.8 143 63.8s107.8-24.7 143-63.8zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm256 16a72 72 0 1 0 0-144 72 72 0 1 0 0 144z"></path>
                        </svg> Profile
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item" href="/pages/profile">View Profile</a></li>
                        <li><a class="dropdown-item" href="/pages/about_us">About Us</a></li>
                        <li><a class="dropdown-item" href="/pages/logout">Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a class="nav-link btn btn-outline-success" href="/pages/login">Login</a></li>
                <li><a class="nav-link btn btn-outline-success" href="/pages/register">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- Include: 
     Bootstrap - pentru functionalitati interactive 
     Popper - pentru pozitionarea elementelor pe pagina (dropdown) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
