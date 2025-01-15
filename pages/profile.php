<?php
session_start();
require_once "../database/db_connect.php";

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['email'];

// Extrage detalii user
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include "../includes/header.php";
include "../includes/navbar.php";
?>

<!-- Afiseaza date profil si butoane catre alte pagini -->
<div class="container mt-5">
    <h2 class="text-center mb-4" style="font-family: 'Times New Roman', serif;">Your Profile</h2>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title text-center" style="font-weight: bold;">Profile Details</h4>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Joined:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($user['is_admin']) && $user['is_admin'] == 1): ?>
    <div class="row mt-5">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Admin Dashboard</h5>
                    <p>View analytics and insights about the website's activity and user behavior.</p>
                    <a href="admin_dashboard" class="btn btn-primary">Go to Admin Dashboard</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Manage Users</h5>
                    <p>View and manage all users on the platform.</p>
                    <a href="manage_users" class="btn btn-primary">Go to Manage Users</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mt-5">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Your Favorites</h5>
                    <p>Manage the articles you have favorited.</p>
                    <a href="favorites" class="btn btn-primary">View Favorites</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Change Password</h5>
                    <p>Update your password to keep your account secure.</p>
                    <a href="reset_password" class="btn btn-warning">Change Password</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
