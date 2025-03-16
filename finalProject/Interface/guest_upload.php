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

$fileToShow = null;
$message = "";  // Mesajları burada tutacağız

// Dosya Yükleme
if (isset($_POST['upload']) && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    if ($fileError === 0) {
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'docx', 'zip');

        if (in_array($fileExt, $allowed) && $fileSize < 10000000) {
            $fileDestination = 'uploads/' . uniqid('', true) . '.' . $fileExt;
            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                // Dosya bilgilerini veritabanına kaydet
                $stmt = $pdo->prepare("INSERT INTO files (file_name, file_path) VALUES (:file_name, :file_path)");
                $stmt->execute([':file_name' => $fileName, ':file_path' => $fileDestination]);
                
                $fileToShow = [
                    'file_name' => $fileName,
                    'file_path' => $fileDestination
                ];
                $message = "<p>Dosya başarıyla yüklendi!</p>";
            } else {
                $message = "<p>Dosya yüklenirken bir hata oluştu.</p>";
            }
        } else {
            $message = "<p>Geçersiz dosya formatı veya dosya çok büyük!</p>";
        }
    } else {
        $message = "<p>Dosya yüklenirken bir hata oluştu.</p>";
    }
}

// Dosya Silme
if (isset($_GET['delete_file'])) {
    $file_path = $_GET['delete_file'];
    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            // Dosya veritabanından sil
            $stmt = $pdo->prepare("DELETE FROM files WHERE file_path = :file_path");
            $stmt->execute([':file_path' => $file_path]);
            $message = "<p>Dosya başarıyla silindi!</p>";
        } else {
            $message = "<p>Dosya silinirken bir hata oluştu.</p>";
        }
    } else {
        $message = "<p>Dosya bulunamadı!</p>";
    }
}

// Dosya İndirme
if (isset($_GET['action']) && $_GET['action'] == 'download' && isset($_GET['file'])) {
    $file = $_GET['file'];
    if (file_exists($file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit();
    } else {
        $message = "<p>Dosya bulunamadı!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Misafir Dosya Yükle</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #F0F0F0;
            color: #333;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            text-align: center; 
        }

        h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #4CAF50;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        input[type="file"] {
            font-size: 16px;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 100%;
            background-color: #fafafa;
            transition: all 0.3s ease;
        }

        input[type="file"]:hover {
            border-color: #4CAF50;
        }

        button {
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            height: 50px;  /* Buton yüksekliklerini eşitlemek için */
            display: inline-block;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Dosya bilgileri kutusu */
        .file-info {
            background-color: #f8f8f8;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
        }

        /* Paylaşılabilir link gizlenmişken ekran dışında kalacak */
        #shareLink {
            display: none; /* Başlangıçta gizli */
            position: absolute;  
            top: 120%; /* Paylaşım linkini daha aşağıya yerleştir */
            left: 50%;
            transform: translateX(-50%); /* Ortalamak için */
            margin-top: 10px;
            word-wrap: break-word;
            max-width: 100%;
            text-align: left;
        }

        /* Link ve butonları düzenleyelim */
        #shareLink a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Butonlar için genel stil */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .action-buttons button { 
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            height: 50px;  /* Buton yüksekliğini eşitlemek için */
        }

        .delete-btn {
            background-color: #FF5733;
        }

        .delete-btn:hover {
            background-color: #D32F2F;
        }

        /* Butonların üzerine gelince renk değişimi */
        .action-buttons button:hover {
            background-color: #45a049;
        }

        /* Paylaş butonunun rengi */
        button.share-btn {
            background-color: #3b82f6;
        }

        button.share-btn:hover {
            background-color: #2563eb;
        }

        /* İndir ve ana sayfaya dön bağlantı stilleri */
        .back-home-link {
            text-decoration: none;
            color: white;
            background-color: #3b82f6;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            display: inline-block;
            margin-top: 10px;
        }

        .back-home-link:hover {
            background-color: #2563eb;
        }

        /* İndir Butonunun tasarımı */
        .btn-download {
            background-color: #3b82f6;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            font-size: 16px; 
            width: 100%; /* Buton genişliğini %100 yapalım */
            display: inline-block;
            text-align: center;
            text-decoration: none;
        }

        .btn-download:hover {
            background-color: #2563eb;
        }

    </style>
    <script>
        function confirmDelete(filePath) {
            if (confirm('Silmek istediğinizden emin misiniz?')) {
                window.location.href = '?delete_file=' + encodeURIComponent(filePath);
            }
        }

        // Paylaş butonuna tıklama fonksiyonu
        function toggleShareLink() {
            var shareLinkDiv = document.getElementById('shareLink');
            if (shareLinkDiv.style.display === "none" || shareLinkDiv.style.display === "") {
                shareLinkDiv.style.display = "block";  // Linki göster
            } else {
                shareLinkDiv.style.display = "none";   // Linki gizle
            }
        }
    </script>
</head>
<body>

    <div class="container">
        <h2>Misafir Olarak Dosya Yükle</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit" name="upload">Dosya Yükle</button>
        </form>

        <?php echo $message; ?> <!-- Mesajları buraya ekliyoruz -->

        <?php if ($fileToShow): ?>
            <div class="file-info">
                <h3>Yüklenen Dosya</h3>
                <p><strong>Dosya Adı:</strong> <?php echo htmlspecialchars($fileToShow['file_name']); ?></p>
                <div class="action-buttons">
                    <a href="?action=download&file=<?php echo urlencode($fileToShow['file_path']); ?>" class="btn-download">İndir</a>
                    <button class="delete-btn" onclick="confirmDelete('<?php echo $fileToShow['file_path']; ?>')">Sil</button>
                    <button class="share-btn" onclick="toggleShareLink()">Paylaş</button>
                    <div id="shareLink">
                        <p><strong>Paylaşılabilir Link:</strong> 
                            <a href="http://localhost/finalProject/Interface/uploads/<?php echo basename($fileToShow['file_path']); ?>" target="_blank">
                                http://localhost/finalProject/Interface/uploads/<?php echo basename($fileToShow['file_path']); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <a href="index.php" class="back-home-link">Ana Sayfaya Dön</a>
    </div>

</body>
</html>
