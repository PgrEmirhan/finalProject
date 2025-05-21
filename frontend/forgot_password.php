<?php
session_start();

require 'connect.php';

$message = "";

// PHPMailer kütüphaneleri
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $input_email = trim($_POST['email']);

    if (!filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Geçersiz e-posta adresi!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$input_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $reset_token = bin2hex(random_bytes(16));
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
            $stmt->execute([$reset_token, $input_email]);

            $reset_link = "http://localhost/finalProject/frontend/reset_password.php?token=" . $reset_token;

            $mail = new PHPMailer(true);
            try {
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'emirhankot423@gmail.com'; // Gmail adresin
                $mail->Password   = 'njof ieco vzkw gqyy'; // Gmail'den aldığın uygulama şifresi
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('emirhankot423@gmail.com', 'Parola Sıfırlama');
                $mail->addAddress($input_email); // Kullanıcının e-postası

                $mail->isHTML(true);
                $mail->Subject = 'Parola Sıfırlama Bağlantısı';
                $mail->Body    = "Parolanızı sıfırlamak için <a href='$reset_link'>buraya tıklayın</a>.<br>Veya bu bağlantıyı kopyalayın: $reset_link";

                $mail->send();
                $message = "✔️ E-posta adresinize sıfırlama bağlantısı gönderildi!";
            } catch (Exception $e) {
                $message = "❌ E-posta gönderilemedi. Hata: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Bu e-posta adresi sistemde kayıtlı değil.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Parola Sıfırlama</title>
</head>
<body>
    <h2>Parolanızı mı unuttunuz?</h2>

    <?php if ($message): ?>
        <p style="color: <?= strpos($message, '✔️') !== false ? 'green' : 'red' ?>;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" action="reset_password.php">
        <label for="email">Kayıtlı E-posta Adresiniz:</label><br>
        <input type="email" name="email" required placeholder="ornek@mail.com">
        <br><br>
        <input type="submit" value="Sıfırlama Linki Gönder">
    </form>
</body>
</html>
