<?php
$host = 'localhost';
$dbname = 'file_sharing';
$username = 'root';
$password = '';

// Veritabanına bağlantı
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı hatası: " . $e->getMessage());
}

if (isset($_GET['id'])) {
    $fileId = $_GET['id'];

    // Dosya bilgilerini veritabanından çekiyoruz
    $stmt = $pdo->prepare("SELECT file_path, file_name FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $filePath = $file['file_path'];
        $fileName = $file['file_name'];

        if (file_exists($filePath)) {
            // İndirme başlatma başlıkları
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
            header('Content-Length: ' . filesize($filePath));

            // Dosyayı okuma ve indirme
            readfile($filePath);
            exit;
        } else {
            echo "Dosya bulunamadı.";
        }
    } else {
        echo "Geçersiz dosya.";
    }
} else {
    echo "Dosya ID'si eksik.";
}
?>
