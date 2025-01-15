<?php
$servername = "sql311.iceiy.com";
$username = "icei_37863787";
$password = "<parola-db>";
$dbname = "icei_37863787_prod";

// Creare conexiune
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificare conexiune
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>