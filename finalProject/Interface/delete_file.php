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
    $stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Dosya sahibinin kim olduğunu kontrol et
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $file['user_id']) {
            // Kullanıcı dosyanın sahibiyse, dosyayı silme işlemi yapılabilir
            $file_path = $file['file_path'];

            // Dosyayı sunucudan sil
            if (file_exists($file_path) && unlink($file_path)) {
                // Dosya veritabanından sil
                $delete_stmt = $pdo->prepare("DELETE FROM files WHERE file_id = ?");
                $delete_stmt->execute([$file_id]);
                echo "Dosya başarıyla silindi.";
            } else {
                echo "Dosya sunucudan silinirken bir hata oluştu.";
            }
        } elseif (isset($_SESSION['guest']) && $_SESSION['guest'] == true) {
            // Misafir kullanıcılar yalnızca kendi yükledikleri dosyaları silebilir
            if ($file['user_id'] == $_SESSION['guest_user_id']) { // Misafir dosyasının sahibi olup olmadığını kontrol et
                $file_path = $file['file_path'];

                // Dosyayı sunucudan sil
                if (file_exists($file_path) && unlink($file_path)) {
                    // Dosya veritabanından sil
                    $delete_stmt = $pdo->prepare("DELETE FROM files WHERE file_id = ?");
                    $delete_stmt->execute([$file_id]);
                    echo "Dosya başarıyla silindi.";
                } else {
                    echo "Dosya sunucudan silinirken bir hata oluştu.";
                }
            } else {
                echo "Misafir kullanıcı yalnızca kendi yüklediği dosyayı silebilir.";
            }
        } else {
            echo "Dosya silme izniniz yok.";
        }
    } else {
        echo "Dosya bulunamadı!";
    }
} else {
    echo "Dosya ID'si belirtilmemiş.";
}
?>
