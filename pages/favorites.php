<?php
session_start();
require_once "../database/db_connect.php";

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    // Dacă utilizatorul nu este autentificat, redirecționează-l către pagina de login
    header("Location: /pages/login.php");
    exit();
}

// Obține ID-ul utilizatorului din sesiune
$userId = $_SESSION['user_id'];

// Pregătește interogarea pentru a obține articolele favorite ale utilizatorului
$stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ?");
$stmt->bind_param("i", $userId); // Asociază parametrul user_id
$stmt->execute();
$result = $stmt->get_result(); // Obține rezultatele interogării

// Creează un array pentru a stoca articolele favorite
$favorites = [];
while ($row = $result->fetch_assoc()) {
    // Adaugă fiecare articol favorit în array-ul $favorites
    $favorites[] = $row;
}

include "../includes/header.php";
include "../includes/navbar.php";

// Gestionează cererile POST pentru ștergerea unui articol favorit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favorite'])) {
    $articleId = $_POST['article_id'];

    // Pregătește interogarea pentru a șterge articolul favorit
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND article_id = ?");
    $stmt->bind_param("is", $userId, $articleId);

    // Execută interogarea și oferă feedback utilizatorului
    if ($stmt->execute()) {
        // Afișează un mesaj de succes și reîncarcă pagina
        echo "<script>alert('Favorite removed successfully.'); window.location.href='favorites.php';</script>";
    } else {
        // Afișează un mesaj de eroare dacă ștergerea eșuează
        echo "<script>alert('Failed to remove favorite.');</script>";
    }
}
?>

<!-- Interfața utilizator pentru afișarea articolelor favorite -->
<div class="container mt-5">
    <h2 class="mb-4 text-center" style="font-family: 'Times New Roman', serif;">Your Favorites</h2>
    <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php if (empty($favorites)): ?>
            <div class="col">
                <div class="alert alert-warning">No favorites yet!</div>
            </div>
        <?php else: ?>
            <?php foreach ($favorites as $favorite): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm" style="font-family: 'Georgia', serif;">
                        <div class="card-body">
                            <h5 class="card-title" style="font-weight: bold;">
                                <a href="<?= htmlspecialchars($favorite['link']) ?>" target="_blank" style="text-decoration: none; color: inherit;">
                                    <?= htmlspecialchars($favorite['title']) ?>
                                </a>
                            </h5>
                        </div>
                        <div class="card-footer text-end d-flex justify-content-between align-items-center">
                            <a href="<?= htmlspecialchars($favorite['link']) ?>" 
                               class="btn btn-sm btn-light" 
                               target="_blank">
                                Read more
                            </a>
                            <!-- Buton sterge de la favorite -->
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="article_id" value="<?= $favorite['article_id'] ?>">
                                <button type="submit" name="remove_favorite" class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
