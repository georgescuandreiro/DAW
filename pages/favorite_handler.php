<?php
session_start();
require_once "../database/db_connect.php";

// Verificăm dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Preluăm datele utilizatorului din sesiune
$userId = $_SESSION['user_id'];

// Preluăm datele trimise prin POST, folosind operatorul null coalescing pentru a preveni erori
$articleId = $_POST['article_id'] ?? '';
$title = $_POST['title'] ?? '';
$link = $_POST['link'] ?? '';

// Verificăm dacă datele sunt complete
if (empty($articleId) || empty($title) || empty($link)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

// Pregătim o interogare pentru a verifica dacă articolul este deja favorit
$stmt = $conn->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND article_id = ?");
$stmt->bind_param("is", $userId, $articleId);
$stmt->execute();
$result = $stmt->get_result();

// Verificăm dacă articolul există deja în lista de favorite
if ($result->num_rows > 0) {
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND article_id = ?");
    $stmt->bind_param("is", $userId, $articleId);
    $stmt->execute();
    // Returnăm un răspuns JSON pentru a indica că articolul a fost eliminat din favorite
    echo json_encode(['status' => 'unfavorited']);
} else {
    // Dacă articolul nu este favorit, pregătim o interogare pentru a-l adăuga în favorite
    $stmt = $conn->prepare("INSERT INTO favorites (user_id, article_id, title, link) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $articleId, $title, $link);
    $stmt->execute();
    // Returnăm un răspuns JSON pentru a indica că articolul a fost adăugat în favorite
    echo json_encode(['status' => 'favorited']);
}
exit();
