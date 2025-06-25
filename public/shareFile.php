<?php 
session_start();  
require 'connect.php';
require 'csrf.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';
$token = bin2hex(random_bytes(16)); 
$share_link = "http://localhost/finalProject/public/download.php?link=" . $token;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file_id = $_POST['file_id'];
    $recipient = $_POST['recipient'];
    $share_link = $_POST['file_link'];
    $share_type = $_POST['shareType'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $expiry_days = intval($_POST['expiry_days']);
    $max_downloads = !empty($_POST['max_downloads']) ? intval($_POST['max_downloads']) : null;

    $expiry_date = date('Y-m-d H:i:s', strtotime("+$expiry_days days"));
    $created_at = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("INSERT INTO shares (file_id, recipient_email, share_type, password, expiry_date, max_downloads, download_count, share_link, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)");
    $stmt->execute([$file_id, $recipient, $share_type, $password, $expiry_date, $max_downloads, $share_link, $created_at]);

 $mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'tefsharing@gmail.com';
    $mail->Password   = 'vmze zuwg xorr vasq';   
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('tefsharing@gmail.com', 'Tef File Sharing'); 
    $mail->addAddress($recipient); 

    $mail->isHTML(true);
    $mail->Subject = 'Dosya Paylaşımı';
    $mail->Body    = "Size bir dosya paylaşıldı: <a href='$share_link'>$share_link</a>";

    $mail->send();
    echo "Paylaşım başarılı. E-posta gönderildi.";
} catch (Exception $e) {
    echo "Paylaşım kaydedildi ama e-posta gönderilemedi. Hata: {$mail->ErrorInfo}";
}

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Dosya Paylaş</title>
</head>
<body>
<form action="shareFile.php" method="POST"> 
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="file_id" value="1">
    
    <label for="recipient">Alıcı E-posta:</label>
    <input type="email" id="recipient" name="recipient" required><br><br>

    <label for="file_link">Paylaşılan Dosya Linki:</label>
    <input type="text" id="file_link" name="file_link" required><br><br>

    <label for="shareType">Paylaşım Türü:</label>
    <select name="shareType" id="shareType">
        <option value="private">Özel</option>
        <option value="public">Genel</option>
    </select><br><br>

    <label for="password">Şifre (isteğe bağlı):</label>
    <input type="text" id="password" name="password"><br><br>

    <label for="expiry_days">Geçerlilik Süresi (gün):</label>
    <input type="number" id="expiry_days" name="expiry_days" value="7"><br><br>

    <label for="max_downloads">Maksimum İndirme Sayısı (isteğe bağlı):</label>
    <input type="number" id="max_downloads" name="max_downloads"><br><br>

    <input type="submit" value="Paylaş">
</form>

</body>
</html>
