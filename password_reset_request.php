<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer-master/src/Exception.php';
require 'phpmailer/PHPMailer-master/src/PHPMailer.php';
require 'phpmailer/PHPMailer-master/src/SMTP.php';
require_once "db.php"; // Połączenie z bazą danych

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Niepoprawny adres e-mail.";
    } else {
        // Sprawdzenie, czy taki email istnieje w bazie danych
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Tworzenie tokena i zapisanie go w bazie danych
            $token = bin2hex(random_bytes(50));
            $query = "INSERT INTO password_resets (email, token) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ss', $email, $token);
            $stmt->execute();

            // Wysyłanie maila z linkiem resetującym
            $reset_link = "https://mateuszoleszko.site/password_reset.php?token=$token";
            $mail = new PHPMailer;

            try {
                // Konfiguracja SMTP
                $mail->isSMTP();
                $mail->Host = ''; // Serwer SMTP twojego dostawcy poczty
                $mail->SMTPAuth = true;
                $mail->Username = ''; // Twój adres e-mail
                $mail->Password = ''; // Twoje hasło SMTP
                $mail->SMTPSecure = 'tls'; // Szyfrowanie TLS
                $mail->Port = 587;

                // Ustawienia nadawcy i odbiorcy
                $mail->setFrom('  ', 'Twojasd strona');
                $mail->addAddress($email);

                // Treść wiadomości
                $mail->isHTML(true);
                $mail->Subject = 'Resetowanie hasła';
                $mail->Body = "Kliknij ten link, aby zresetować swoje hasło: <a href='$reset_link'>$reset_link</a>";

                if ($mail->send()) {
                    $success_message = "Link resetujący hasło został wysłany na twój adres e-mail.";
                } else {
                    $errors[] = "Nie udało się wysłać wiadomości: " . $mail->ErrorInfo;
                }
            } catch (Exception $e) {
                $errors[] = "Wysyłanie e-maila nie powiodło się: {$mail->ErrorInfo}";
            }
        } else {
            $errors[] = "Nie znaleziono konta powiązanego z tym adresem e-mail.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset hasła</title>
</head>
<body>
    <h1>Reset hasła</h1>

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

    <form method="POST">
        <label for="email">Podaj swój e-mail:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Wyślij link resetujący</button>
    </form>
</body>
</html>
