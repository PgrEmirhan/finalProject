<?php
session_start();
require 'connect.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Yetkisiz erişim");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['file'])) {
    die("Hatalı istek.");
}

$filePath = $_POST['file'];

// Kullanıcının bu dosyaya sahip olup olmadığını kontrol et
$stmt = $pdo->prepare("SELECT * FROM files WHERE file_path = ? AND user_id = ?");
$stmt->execute([$filePath, $user_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    die("Dosya bulunamadı veya yetkiniz yok.");
}

$fullPath = __DIR__ . '/' . $filePath;

if (!file_exists($fullPath)) {
    die("Dosya fiziksel olarak mevcut değil.");
}

// MIME türünü tespit et
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $fullPath);
finfo_close($finfo);

// Dosyayı tarayıcıda göster
header("Content-Type: $mimeType");
header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
readfile($fullPath);
exit;
?>
