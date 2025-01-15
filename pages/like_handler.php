<?php
session_start();
require_once "../database/db_connect.php";

// Verifica daca userul este logat
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];
$articleId = $_POST['article_id'] ?? '';

if (empty($articleId)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid article ID']);
    exit();
}

// Verifica daca userul a dat deja like la articol
$stmt = $conn->prepare("SELECT 1 FROM likes WHERE user_id = ? AND article_id = ?");
$stmt->bind_param("is", $userId, $articleId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Daca userul a dat deja like la aritcol, il sterge
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND article_id = ?");
    $stmt->bind_param("is", $userId, $articleId);
    $stmt->execute();
    echo json_encode(['status' => 'unliked']);
} else {
    // Adauga like
    $stmt = $conn->prepare("INSERT INTO likes (user_id, article_id) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $articleId);
    $stmt->execute();
    echo json_encode(['status' => 'liked']);
}

exit();
