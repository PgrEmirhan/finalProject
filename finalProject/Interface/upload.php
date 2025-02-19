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

if (isset($_POST['submit']) && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // Dosya adı ve yolu
    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileError = $_FILES['file']['error'];

    if ($fileError === 0) {
        // Dosyanın uzantısını kontrol et
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'docx', 'zip');

        if (in_array($fileExt, $allowed)) {
            if ($fileSize < 10000000) { // Maksimum 10MB
                $fileDestination = 'C:/xampp/htdocs/finalProject/uploads/' . uniqid('', true) . '.' . $fileExt;
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Dosya bilgilerini veritabanına kaydet
                    $sql = "INSERT INTO files (file_name, file_path) VALUES (?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$fileName, $fileDestination]);

                    echo "Dosya başarıyla yüklendi!";
                } else {
                    echo "Dosya yüklenirken bir hata oluştu.";
                }
            } else {
                echo "Dosya çok büyük, lütfen 10MB'dan küçük bir dosya yükleyin.";
            }
        } else {
            echo "Geçersiz dosya formatı!";
        }
    } else {
        echo "Dosya yüklenirken bir hata oluştu.";
    }
}

// Dosya bilgilerini veritabanından alıyoruz
$stmt = $pdo->query("SELECT * FROM files");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Yükleme ve Paylaşım</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Dosya Yükle</h2>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <label for="file">Dosya Seç:</label>
            <input type="file" name="file" id="file" required>
            <button type="submit" name="submit">Yükle</button>
        </form>

        <h2>Yüklenen Dosyalar</h2>
        <ul id="file-list">
            <!-- Yüklenen dosyalar burada görünecek -->
            <?php
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $fileUrl = 'uploads/' . basename($row['file_path']);
                    $shareLink = "http://localhost/finalProject/" . $fileUrl; // Paylaşılabilir link
                    $fileExt = strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));

                    echo "<li>";
                    echo $row['file_name'];
                    echo " <button class='download-btn' data-id='" . $row['id'] . "'>İndir</button>"; // İndirme butonu
                    echo " | <a href='" . $shareLink . "' target='_blank'>Paylaş</a>"; // Paylaşma linki
                    echo " | <a href='delete.php?id=" . $row['id'] . "'>Sil</a>"; // Silme butonu
                    echo "</li>";
                }
            ?>
        </ul>
    </div>

    <script src="scripts.js"></script>

    <script>
        // AJAX ile dosya indirme işlemi
        document.querySelectorAll('.download-btn').forEach(button => {
            button.addEventListener('click', function() {
                var fileId = this.getAttribute('data-id');
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'download.php?id=' + fileId, true);
                xhr.responseType = 'blob'; // Dosya olarak yanıt alıyoruz

                xhr.onload = function() {
                    var blob = xhr.response;
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "dosya." + blob.type.split("/")[1]; // Dosya uzantısına göre adlandırma
                    link.click(); // İndirme başlatma
                };

                xhr.send();
            });
        });
    </script>
</body>
</html>
