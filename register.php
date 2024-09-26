<?php
require_once "db.php";
require_once "functions.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Walidacja formularza
    if (empty($username)) {
        $errors[] = "Nazwa użytkownika jest wymagana.";
    }

    if (!validateEmail($email)) {
        $errors[] = "Niepoprawny adres email.";
    }

    if (!validatePassword($password)) {
        $errors[] = "Hasło musi mieć co najmniej 8 znaków.";
    }

    if ($password !== $password_confirm) {
        $errors[] = "Hasła nie są zgodne.";
    }

    // Sprawdzanie, czy użytkownik już istnieje
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errors[] = "Użytkownik o podanym adresie email już istnieje.";
    }

    // Jeśli nie ma błędów, dodajemy użytkownika
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sss', $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            header("Location: login.php?registered=true");
            exit();
        } else {
            $errors[] = "Wystąpił błąd podczas rejestracji.";
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
    <title>Rejestracja</title>
</head>
<body>
    <h1>Rejestracja</h1>

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
        <label for="username">Nazwa użytkownika:</label>
        <input type="text" id="username" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        <br>
        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <label for="password_confirm">Potwierdź hasło:</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
        <br>
        <button type="submit">Zarejestruj się</button>
    </form>
    <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
</body>
</html>
