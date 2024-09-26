<?php
// Włączanie wyświetlania błędów
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php'; // Połączenie z bazą danych

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];

    // Sprawdzenie tokena
    $query = "SELECT * FROM password_resets WHERE token = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Błąd przygotowania zapytania: " . $conn->error);
    }
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Formularz resetowania hasła
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password !== $confirm_password) {
                $errors[] = "Hasła się nie zgadzają.";
            } else {
                // Pobierz e-mail powiązany z tokenem
                $row = $result->fetch_assoc();
                $email = $row['email'];

                // Zaktualizuj hasło w bazie danych
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE users SET password = ? WHERE email = ?";
                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    die("Błąd przygotowania zapytania: " . $conn->error);
                }
                $stmt->bind_param('ss', $new_password_hash, $email);

                if ($stmt->execute()) {
                    // Usuń token po pomyślnym zaktualizowaniu hasła
                    $query = "DELETE FROM password_resets WHERE token = ?";
                    $stmt = $conn->prepare($query);
                    if (!$stmt) {
                        die("Błąd przygotowania zapytania: " . $conn->error);
                    }
                    $stmt->bind_param('s', $token);
                    $stmt->execute();

                    $success_message = "Hasło zostało pomyślnie zresetowane. <a href='login.php'>Zaloguj się</a>";
                } else {
                    $errors[] = "Błąd podczas aktualizacji hasła: " . $stmt->error;
                }
            }
        }
    } else {
        $errors[] = "Nieprawidłowy token.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetowanie hasła</title>
</head>
<body>
    <h1>Resetowanie hasła</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="success-message">
            <?php echo $success_message; ?>
        </div>
    <?php else: ?>
        <form method="POST">
            <label for="password">Nowe hasło:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <label for="confirm_password">Potwierdź nowe hasło:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <br>
            <button type="submit">Zresetuj hasło</button>
        </form>
    <?php endif; ?>
</body>
</html>
