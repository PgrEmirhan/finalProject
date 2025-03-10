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

if (isset($_GET['id'])) {
    $file_id = $_GET['id'];

    // Dosyayı veritabanından al
    $stmt = $pdo->prepare("SELECT * FROM files WHERE ID = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $file_name = $file['file_name'];
        $file_path = $file['file_path'];

        // Paylaşılacak URL
        $share_url = "http://localhost/" . $file_path;

        // Paylaşım linkini göster
        echo "Dosya Paylaşım Linki: <a href='$share_url' target='_blank'>$share_url</a>";
    } else {
        echo "Dosya bulunamadı!";
    }
}
?>
