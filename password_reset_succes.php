<?php
require_once "db.php"; // Połączenie z bazą danych
require_once "functions.php"; // Ewentualne dodatkowe funkcje

$token = $_GET['token'] ?? '';
$errors = [];
$success_message = '';

// Sprawdzamy, czy token istnieje
$query = "SELECT * FROM password_resets WHERE token = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Nieprawidłowy lub wygasły token.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Walidacja hasła
    if (strlen($password) < 8) {
        $errors[] = "Hasło musi mieć co najmniej 8 znaków.";
    }

    if ($password !== $password_confirm) {
        $errors[] = "Hasła nie są zgodne.";
    }

    if (empty($errors)) {
        // Pobieranie emaila z tabeli password_resets
        $row = $result->fetch_assoc();
        $email = $row['email'];

        // Hashowanie nowego hasła
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Aktualizacja hasła w tabeli users
        $query = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $hashed_password, $email);
        if ($stmt->execute()) {
            // Usuwanie tokena z tabeli password_resets
            $query = "DELETE FROM password_resets WHERE token = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $token);
            $stmt->execute();

            $success_message = "Hasło zostało zresetowane pomyślnie. Możesz się teraz zalogować.";
        } else {
            $errors[] = "Wystąpił błąd podczas resetowania hasła.";
        }
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
    <h1>Zresetuj swoje hasło</h1>

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
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>


</body>
</html>
