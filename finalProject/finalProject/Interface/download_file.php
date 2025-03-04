<?php
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

if (isset($_GET['id'])) {
    $fileId = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $filePath = $file['file_path'];
        if (file_exists($filePath)) {
            // Dosya başlıklarını ayarla
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath); // Dosyayı oku ve gönder
            exit;
        } else {
            echo "Dosya bulunamadı.";
        }
    } else {
        echo "Geçersiz dosya ID'si.";
    }
} else {
    echo "Dosya ID'si belirtilmemiş.";
}
?>
