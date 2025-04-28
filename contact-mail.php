<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['kadi'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    // Basit doğrulama
    if (empty($name) || empty($email) || empty($message)) {
        die("Lütfen tüm alanları doldurun.");
    }

    $mail = new PHPMailer(true);

    try {
        // Sunucu ayarları
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'emirhankot423@gmail.com'; // Gmail adresiniz
        $mail->Password   = 'njof ieco vzkw gqyy';    // Gmail uygulama şifresi
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet = 'UTF-8';   

        // Gönderen / Alıcı
        $mail->setFrom('emirhankot423@gmail.com', 'İletişim Formu');
        $mail->addAddress('emirhankot423@gmail.com'); // Kendinize veya destek adresinize gönderin

        // İçerik
        $mail->isHTML(false);
        $mail->Subject = 'Yeni İletişim Formu Mesajı';
        $mail->Body    = "Adı: $name\nEmail: $email\nMesaj:\n$message";

        $mail->send();
        echo "<script>alert('Mesaj başarıyla gönderildi!'); window.location.href='contact.php';</script>";
    } catch (Exception $e) {
        echo "Mesaj gönderilemedi. Hata: {$mail->ErrorInfo}";
    }
}
?>
