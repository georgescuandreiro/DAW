<?php
// Dezactivarea afisarii erorilor pe ecran
ini_set('display_errors', 1);  // Activeaza error reporting for debugging
error_reporting(E_ALL);        // Arata erorile

// Porneste sesiunea daca nu este pornita deja
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include conexiunea la baza de date
require_once "../database/db_connect.php";

// Seteaza zona de timp Romania Time (EET/EEST)
date_default_timezone_set('Europe/Bucharest'); // Zona Europe/Bucharest

// Captureaza date pentru analytics
function captureUniqueAnalytics($conn)
{
    // Captureaza pagina si adresa IP
    $page = $_SERVER['REQUEST_URI']; // URL pagina curenta
    $visitor_ip = $_SERVER['REMOTE_ADDR']; // Adresa IP a vizitatorului

    // Detecteaza SO (sistemul de operare ) din stringul de User-Agent
    $os = 'Unknown OS';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/Windows/i', $userAgent)) {
        $os = 'Windows';
    } elseif (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
        $os = 'MacOS';
    } elseif (preg_match('/Linux/i', $userAgent)) {
        $os = preg_match('/Android/i', $userAgent) ? 'Linux; Android' : 'Linux';
    } elseif (preg_match('/iPhone|iPad/i', $userAgent)) {
        $os = 'iOS';
    }

    // Detecteaza tipul de device
    $device_type = 'Desktop'; // Default este Desktop
    if (preg_match('/mobile/i', $userAgent)) {
        $device_type = 'Mobile';
    } elseif (preg_match('/tablet/i', $userAgent)) {
        $device_type = 'Tablet';
    }

    // Detect browserul
    $browser = 'Unknown Browser';
    if (strpos($userAgent, 'Chrome') !== false) {
        $browser = 'Chrome';
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        $browser = 'Firefox';
    } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
        $browser = 'Safari';
    } elseif (strpos($userAgent, 'Edge') !== false) {
        $browser = 'Edge';
    } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
        $browser = 'Internet Explorer';
    }

    // Pagina de referinta
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'Direct';

    // Captureaza zona curenta (Romania)
    $currentTime = date('Y-m-d H:i:s');

    // Verifica daca vizita este unica (aceeasi pagina, acelasi IP, interval de 24 ore)
    $query = "SELECT COUNT(*) AS visit_count 
              FROM analytics 
              WHERE page = ? AND visitor_ip = ? AND visit_time >= NOW() - INTERVAL 1 DAY";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param('ss', $page, $visitor_ip);
        $stmt->execute();
        $stmt->bind_result($visit_count);
        $stmt->fetch();
        $stmt->close();

        // Daca nu exista nici o intrare in 24 ore, insereaza vizita noua
        if ($visit_count == 0) {
            $insertQuery = "INSERT INTO analytics (page, visitor_ip, visit_time, os, device_type, browser, referrer) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            if ($insertStmt) {
                $insertStmt->bind_param('sssssss', $page, $visitor_ip, $currentTime, $os, $device_type, $browser, $referrer);
                $insertStmt->execute();
                $insertStmt->close();
            } else {
                error_log("Failed to prepare insert query: " . $conn->error);
            }
        }
    } else {
        error_log("Failed to prepare unique visit check query: " . $conn->error);
    }
}

// Cheama functia pentru a captura datele analytics
captureUniqueAnalytics($conn);
?>

<!-- HEADER -->

<!-- Logo site - trimite catre dashboard (pagina principala) -->
<div class="logo-container">
    <a href="/pages/dashboard">
        <img src="/assets/logo.png" alt="Logo" class="site-logo">
    </a>
</div>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Header with Analytics</title>
    
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    
    <!-- Include Custom Styles -->
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/dashboard.css">
    
    <!-- Font Awesome (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOMZdlt9bgDPvMYSTsA9d+xFdW2AZJa5/twLI57d" crossorigin="anonymous">

    <!-- Google Fonts (Optional) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
