<?php
$host = 'localhost';
$db   = 'users_db';
$user = 'root';
$pass = 'spawn666'; // Twoje hasło do bazy danych

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}
?>
