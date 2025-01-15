<!-- HEADER CUSTOM PENTRU PAGINILE LOGIN/REGISTER -->

<?php
// Incepe sesiunea daca nu este deja inceputa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Cod HTML + bootstrap pagina de logare/register -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Echo News Magazine</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS custom -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
