<?php
// Veritabanı bağlantısı
$host = 'localhost';
$dbname = 'file_sharing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı hatası: " . $e->getMessage());
}

// Token kontrolü
if (!isset($_GET['token'])) {
    die("Geçersiz bağlantı.");
}

$token = $_GET['token'];

// Paylaşımı veritabanından al
$stmt = $pdo->prepare("SELECT s.*, f.file_path FROM shares s JOIN files f ON s.file_id = f.file_id WHERE s.share_link LIKE ?");
$stmt->execute(["%$token"]);
$share = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$share) {
    die("Paylaşım bulunamadı.");
}

// Süre kontrolü
if (new DateTime() > new DateTime($share['expiry_date'])) {
    die("Bağlantının süresi dolmuş.");
}

// İndirme limiti kontrolü
if ($share['max_downloads'] !== null && $share['download_count'] >= $share['max_downloads']) {
    die("İndirme limiti aşıldı.");
}

// Parola gerekiyorsa ve daha girilmemişse formu göster
if ($share['share_type'] === 'private' && !empty($share['password'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $enteredPassword = $_POST['password'];
        if (!password_verify($enteredPassword, $share['password'])) {
            die("Parola yanlış.");
        }
    } else {
        echo '<form method="POST">
                <label>Parola:</label>
                <input type="password" name="password" required>
                <input type="submit" value="Eriş">
              </form>';
        exit();
    }
}

// İndirme sayısını güncelle
$update = $pdo->prepare("UPDATE shares SET download_count = download_count + 1 WHERE share_id = ?");
$update->execute([$share['share_id']]);

// Dosyayı gönder
$filePath = $share['file_path'];
if (!file_exists($filePath)) {
    die("Dosya bulunamadı.");
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit();
?>
