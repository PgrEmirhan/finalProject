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

// Anonim dosya yükleme
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Dosya yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
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
                $fileDestination = __DIR__ . '/uploads/' . uniqid('', true) . '.' . $fileExt;
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Eğer kullanıcı giriş yapmışsa, user_id ile ekle
                    $sql = "INSERT INTO files (file_name, file_path, user_id) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$fileName, $fileDestination, $user_id]);

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
?>

<!-- Dosya Yükleme Formu -->
<form action="upload.php" method="POST" enctype="multipart/form-data">
    <label for="file">Dosya Seç:</label>
    <input type="file" name="file" required><br><br>
    <button type="submit">Dosya Yükle</button>
</form>


    <hr>

    <!-- Kullanıcının Yüklediği Eski Dosyalar -->
    <h3>Yüklediğiniz Dosyalar:</h3>
    <?php if (count($files) > 0): ?>
        <ul>
            <?php foreach ($files as $file): ?>
                <li>
                    <?php echo htmlspecialchars($file['file_name']); ?> - 
                    <!-- Dosya İndir Butonu -->
                    <a href="download_file.php?file_id=<?php echo $file['ID']; ?>">İndir</a> | 
                    <!-- Dosya Sil Butonu -->
                    <a href="delete_file.php?id=<?php echo $file['ID']; ?>">Sil</a> | 
                    <!-- Dosya Paylaş Butonu -->
                    <a href="share_file.php?id=<?php echo $file['ID']; ?>">Paylaş</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Henüz dosya yüklemediniz.</p>
    <?php endif; ?>

    <!-- Çıkış Butonu -->
    <form action="upload.php" method="POST">
        <button type="submit" name="logout">Çıkış Yap</button>
    </form>

</body>
</html>
