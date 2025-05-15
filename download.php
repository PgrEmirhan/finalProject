<?php
session_start();

require 'connect.php';

$share_link = $_GET['link'];

// Paylaşımı getir
$stmt = $pdo->prepare("SELECT * FROM shares WHERE share_link = ?");
$stmt->execute([$share_link]);
$share = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$share) {
    die("Paylaşım bulunamadı.");
}

// Süre kontrolü
if ($share['expiry_date'] && strtotime($share['expiry_date']) < time()) {
    die("Paylaşım süresi dolmuş.");
}

// İndirme sınırı kontrolü
if ($share['max_downloads'] && $share['download_count'] >= $share['max_downloads']) {
    die("İndirme limiti dolmuş.");
}

// Şifre kontrolü
$token = $share['share_link'];

if (!empty($share['password'])) {
    // Şifre doğrulanmamışsa sor
    if (!isset($_SESSION['authenticated_links'][$token])) {
        // Eğer form gönderildiyse, şifreyi kontrol et
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $input_password = $_POST['password'];
            if (password_verify($input_password, $share['password'])) {
                $_SESSION['authenticated_links'][$token] = true; // Bu token doğrulandı
                // Yönlendirerek formun yeniden gönderilmesini engelle
                header("Location: download.php?link=" . urlencode($token));
                exit;
            } else {
                echo "❌ Hatalı şifre. <a href=\"?link=$token\">Tekrar dene</a>";
                exit;
            }
        } else {
            // Şifre formu
            echo '<form method="POST">
                    <label>🔐 Şifre:</label>
                    <input type="password" name="password" required>
                    <button type="submit">İndir</button>
                  </form>';
            exit;
        }
    }
}

// Dosya bilgisi
$stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ?");
$stmt->execute([$share['file_id']]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    die("Dosya bulunamadı.");
}

$filePath = $file['file_path'];
$fileName = $file['file_name'];

if (!file_exists($filePath)) {  
    die("Dosya sunucuda bulunamadı.");
}

// İndirme sayısını güncelle
$stmt = $pdo->prepare("UPDATE shares SET download_count = download_count + 1 WHERE share_id = ?");
$stmt->execute([$share['share_id']]);

// Dosyayı indir
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
?>
