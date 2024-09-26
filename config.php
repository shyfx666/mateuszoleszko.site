<?php
$servername = ""; // Zmień na nazwę swojego serwera, jeśli jest inna
$username = "";
$password = "";
$dbname = "";

// Połączenie z bazą danych
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);   

},
?>