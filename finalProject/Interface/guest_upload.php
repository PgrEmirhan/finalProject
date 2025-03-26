<?php
session_start();

// Veritabanı bağlantısı
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
$userId = null;

// Kullanıcı kontrolü
if (isset($_SESSION['user_id'])) {
    // Kayıtlı kullanıcı girişi
    $userId = $_SESSION['user_id'];
} elseif (isset($_SESSION['guest']) && $_SESSION['guest'] == true) {
    // Misafir kullanıcı girişi
    $userId = null;  // Misafirler için user_id yok
}

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
                $stmt = $pdo->prepare("INSERT INTO files (file_name, file_path, uploaded_at, user_id) 
                                       VALUES (:file_name, :file_path, NOW(), :user_id)");

                // Misafir kullanıcı ise user_id olarak NULL kaydedilecek, aksi takdirde giriş yapan kullanıcının ID'si
                $stmt->execute([
                    ':file_name' => $fileName,
                    ':file_path' => $fileDestination,
                    ':user_id' => $userId
                ]);

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

    // Dosyayı yükleyen kullanıcı ile silme işlemi yapılıyor
    $stmt = $pdo->prepare("SELECT * FROM files WHERE file_path = :file_path");
    $stmt->execute([':file_path' => $file_path]);
    $file = $stmt->fetch();

    if ($file) {
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

// 24 saatten eski dosyaları silme
$stmt = $pdo->prepare("DELETE FROM files WHERE uploaded_at < NOW() - INTERVAL 1 DAY");
$stmt->execute();
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
            height: 50px;
            display: inline-block;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        .file-info {
            background-color: #f8f8f8;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
        }

        #shareLink {
            display: none;
            position: absolute;
            top: 120%;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 10px;
            word-wrap: break-word;
            max-width: 100%;
            text-align: left;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            margin-bottom: 50px;
        }

        #shareLink a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .delete-btn {
            background-color: #FF5733;
        }

        .delete-btn:hover {
            background-color: #D32F2F;
        }

        .action-buttons button:hover {
            background-color: #45a049;
        }

        button.share-btn {
            background-color: #3b82f6;
        }

        button.share-btn:hover {
            background-color: #2563eb;
        }

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

        .btn-download {
            background-color: #3b82f6;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            font-size: 16px; 
            width: 100%;
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
            var confirmDelete = confirm("Dosyayı silmek istediğinizden emin misiniz?");
            if (confirmDelete) {
                window.location.href = "?delete_file=" + encodeURIComponent(filePath);
            }
        }

        function toggleShareLink() {
            var shareLink = document.getElementById("shareLink");
            var filePath = "<?php echo 'http://localhost/finalProject/Interface/' . $fileToShow['file_path']; ?>";

            if (shareLink.style.display === "block") {
                shareLink.style.display = "none";
            } else {
                var linkHtml = `
                    <p><strong>Paylaşılabilir Link:</strong> 
                        <a href="${filePath}" target="_blank">${filePath}</a>
                    </p>
                `;
                shareLink.innerHTML = linkHtml;
                shareLink.style.display = "block";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Dosya Yükle</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit" name="upload">Dosya Yükle</button>
        </form>
        <p style="color:red;">* Dosyalarınız geçici olarak yüklenir ve sayfa yenilendiğinde silinmektedir. *</p>

        <?php echo $message; ?>

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
