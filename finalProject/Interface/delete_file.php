<?php
$host = 'localhost'; // Veritabanı sunucusu
$dbname = 'file_sharing'; // Veritabanı adı
$username = 'root'; // Kullanıcı adı
$password = ''; // Parola

// Veritabanına bağlantı
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı hatası: " . $e->getMessage());
}

// Silinecek dosyanın ID'si alınıyor
if (isset($_GET['id'])) {
    $fileId = $_GET['id'];

    // Veritabanından dosya bilgisini al
    $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Dosya sisteminden dosyayı sil
        $filePath = $file['file_path'];
        if (unlink($filePath)) {
            // Dosya başarıyla silindi, veritabanından da kaydı sil
            $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
            $stmt->execute([$fileId]);

            echo "Dosya başarıyla silindi.";
        } else {
            echo "Dosya silinirken bir hata oluştu.";
        }
    } else {
        echo "Dosya bulunamadı.";
    }
} else {
    echo "Geçersiz istek.";
}
echo "<a href='upload.php'>İşlem ekranına dön</a>"
echo "<a href='index.html'>Anasayfaya dön</a>"
?>
