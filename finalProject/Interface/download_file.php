<?php
session_start();

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

if (isset($_GET['file_id'])) {
    $file_id = $_GET['file_id'];

    // Dosyayı veritabanından al
    $stmt = $pdo->prepare("SELECT * FROM files WHERE ID = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $file_path = $file['file_path'];
        if (file_exists($file_path)) {
            // Dosyayı indir
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit();
        } else {
            echo "Dosya bulunamadı.";
        }
    } else {
        echo "Geçersiz dosya.";
    }
}
?>
