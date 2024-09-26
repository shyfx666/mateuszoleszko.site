<?php

// Sprawdzenie, czy użytkownik jest zalogowany
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Przekierowanie użytkownika, jeśli nie jest zalogowany
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Walidacja emaila
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Walidacja hasła (minimum 8 znaków, możliwość wprowadzenia bardziej rygorystycznych zasad)
function validatePassword($password) {
    return strlen($password) >= 8;
}
?>
