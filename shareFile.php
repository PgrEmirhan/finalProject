<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
} 
// PHPMailer kütüphaneleri
require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Alınan veriler
    $recipientEmail = $_POST['recipient'];  // Alıcı e-posta adresi
    $fileLink = $_POST['file_link'];  // Paylaşılan dosya linki

    // Dosya adı ve mesaj içeriği
    $subject = "Paylaşılan Dosya Linki";
    $message = "Merhaba,\n\nBu mesaj, bir dosya paylaşımı içermektedir. Aşağıdaki linkten dosyayı indirebilirsiniz:\n\n" . $fileLink . "\n\nİyi günler.";

    // PHPMailer ile e-posta gönderimi
    $mail = new PHPMailer(true);

    try {
        // SMTP yapılandırması
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // SMTP sunucusu (Gmail kullanıyorsanız)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'emirhankot423@gmail.com';  // Buraya kendi Gmail adresinizi yazın
        $mail->Password   = 'njof ieco vzkw gqyy';  // Buraya Gmail şifrenizi yazın (Yoksa uygulama şifresi kullanabilirsiniz)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet = 'UTF-8';

        // Gönderen e-posta bilgileri
        $mail->setFrom('your-email@gmail.com', 'Dosya Paylaşımı');  // Burada kendi e-posta adresinizi kullanın
        $mail->addAddress($recipientEmail);  // Alıcı e-posta adresi

        // E-posta içeriği
        $mail->Subject = $subject;
        $mail->Body    = $message;

        // E-posta gönderme işlemi
        if ($mail->send()) {
            echo "Dosya başarıyla paylaşıldı!";
        } else {
            echo "E-posta gönderilemedi. Hata: " . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        echo "E-posta gönderilemedi. Hata: " . $mail->ErrorInfo;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Paylaş</title>
</head>
<body>
    <h1>Dosya Paylaş</h1>
    <form action="shareFile.php" method="POST">
        <label for="recipient">Alıcı E-posta:</label>
        <input type="email" id="recipient" name="recipient" required><br><br>

        <label for="file_link">Paylaşılan Dosya Linki:</label>
        <input type="text" id="file_link" name="file_link" required><br><br>

        <input type="submit" value="Paylaş">
    </form>
</body>
</html>
