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

// Dosya bilgilerini veritabanından alıyoruz
$stmt = $pdo->query("SELECT * FROM files");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Paylaşımı</title>
</head>
<body>
    <h2>Yüklenen Dosyalar</h2>
    <ul>
        <?php
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $fileUrl = 'http://localhost/finalProject/' . $row['file_path']; // Paylaşılabilir link
                echo "<li>";
                echo $row['file_name'];
                echo " <a href='" . $fileUrl . "' download>İndir</a> | ";
                echo "<a href='" . $fileUrl . "' target='_blank'>Paylaş</a>"; // Paylaşma linki
                echo "</li>";
            }
        ?>
    </ul>
</body>
</html>
