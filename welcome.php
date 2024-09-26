<?php
session_start();
require_once "functions.php";

// Wymagamy zalogowania użytkownika
requireLogin();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Witaj</title>
</head>
<body>
    <h1>Witaj, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>Zalogowałeś się pomyślnie.</p>
    <a href="logout.php">Wyloguj się</a>
</body>
</html>
