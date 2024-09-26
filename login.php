<?php
require_once "db.php";
require_once "functions.php";
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Walidacja emaila i hasła
    if (!validateEmail($email)) {
        $errors[] = "Niepoprawny adres email.";
    }

    if (empty($password)) {
        $errors[] = "Hasło jest wymagane.";
    }

    // Szukanie użytkownika w bazie danych
    if (empty($errors)) {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Sprawdzanie hasła
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: welcome.php");
                exit();
            } else {
                $errors[] = "Błędne hasło.";
            }
        } else {
            $errors[] = "Nie znaleziono użytkownika.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Logowanie</title>
</head>
<body>
    <h1>Logowanie</h1>

    <?php if (isset($_GET['registered'])): ?>
        <div class="success-message">
            Rejestracja zakończona sukcesem! Możesz się teraz zalogować.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        <br>
        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Zaloguj się</button>
    </form>
    <p>Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
    <p>Nie pamiętasz hasła? <a href="password_reset_request.php">Reset Hasła</a></p>
</body>
</html>
