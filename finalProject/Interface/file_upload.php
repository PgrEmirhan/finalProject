<!-- file_upload.php -->
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


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

if (isset($_POST['submit']) && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Dosya yükleme işlemi...
}
// Dosya yükleme işlemi sonrası anonim kullanıcıya dosyanın linkini göster
if (isset($file)) {
    // Dosya yükleme işlemi
    // (Örneğin: $file_path, dosya yolu)
    echo "Dosyanız başarıyla yüklendi. Linkinizi buradan kopyalayabilirsiniz: <br>";
    echo "<a href='" . $file_path . "' target='_blank'>" . $file_path . "</a>";
}
$file_tmp = $_FILES['file']['tmp_name'];
$file_name = $_FILES['file']['name'];
$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

// Dosyanın kaydedileceği yol
$upload_dir = 'uploads/';
$upload_path = $upload_dir . uniqid('', true) . '.' . $file_ext;

// Dosyayı kaydet
if (move_uploaded_file($file_tmp, $upload_path)) {
    // Veritabanına ekle
    $stmt = $pdo->prepare("INSERT INTO files (file_name, file_path) VALUES (?, ?)");
    $stmt->execute([$file_name, $upload_path]);

    echo "Dosyanız başarıyla yüklendi. Linkinizi buradan kopyalayabilirsiniz: <br>";
    echo "<a href='" . $upload_path . "' target='_blank'>" . $upload_path . "</a>";
} else {
    echo "Dosya yüklenirken bir hata oluştu!";
}

?>
