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
        $file_path = $file['file_path'];
        if (unlink($file_path)) { // Dosya sunucudan sil
            // Dosyayı veritabanından sil
            $delete_stmt = $pdo->prepare("DELETE FROM files WHERE ID = ?");
            $delete_stmt->execute([$file_id]);
            echo "Dosya başarıyla silindi.";
        } else {
            echo "Dosya silinirken bir hata oluştu.";
        }
    } else {
        echo "Dosya bulunamadı!";
    }
}
?>
