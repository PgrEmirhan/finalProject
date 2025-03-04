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

// Silme işlemi için ID alınıyor
if (isset($_GET['id'])) {
    $fileId = $_GET['id'];

    // Veritabanından dosya bilgisini al
    $stmt = $pdo->prepare("SELECT file_path, file_name FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Dosya yolunu tam olarak belirleyin (uploads dizini ile)
        $filePath = 'C:/xampp/htdocs/finalProject/' . $file['file_path'];

        // Dosya sisteminden dosyayı sil
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                // Dosya başarıyla silindi, veritabanından da kaydı sil
                $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
                $stmt->execute([$fileId]);

                $message = "Dosya başarıyla silindi.";
            } else {
                $message = "Dosya silinirken bir hata oluştu.";
            }
        } else {
            $message = "Dosya bulunamadı. Lütfen dosya yolunu kontrol edin.";
        }
    } else {
        $message = "Dosya bulunamadı.";
    }
} else {
    $message = "Geçersiz istek.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Silme İşlemi</title>
</head>
<body>
    <div>
        <p><?php echo $message; ?></p>
        <a href="upload.php">Dosya Yükleme Sayfasına Dön</a><br>
        <a href="index.html">Anasayfaya Dön</a>
    </div>
</body>
</html>
