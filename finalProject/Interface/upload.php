<?php
session_start();

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Giriş yapılmamışsa login sayfasına yönlendir
    exit();
}

// Çıkış işlemi
if (isset($_POST['logout'])) {
    session_unset(); // Oturum verilerini temizle
    session_destroy(); // Oturumu sonlandır
    header("Location: login.php"); // Giriş sayfasına yönlendir
    exit();
}

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

$user_id = $_SESSION['user_id'];
$username = $_SESSION['user_name']; // Kullanıcı adını oturumdan al

// Dosya yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileError = $_FILES['file']['error'];

    $uploadMessage = ''; // Mesaj için değişken

    if ($fileError === 0) {
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'docx', 'zip');

        if (in_array($fileExt, $allowed)) {
            if ($fileSize < 10000000) { 
                $fileDestination = __DIR__ . '/uploads/' . uniqid('', true) . '.' . $fileExt;
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Dosyayı veritabanına ekle
                    $sql = "INSERT INTO files (file_name, file_path, user_id) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$fileName, $fileDestination, $user_id]);

                    $uploadMessage = "<p class='success-msg'>Dosya başarıyla yüklendi!</p>";
                } else {
                    $uploadMessage = "<p class='error-msg'>Dosya yüklenirken bir hata oluştu.</p>";
                }
            } else {
                $uploadMessage = "<p class='error-msg'>Dosya çok büyük, lütfen 10MB'dan küçük bir dosya yükleyin.</p>";
            }
        } else {
            $uploadMessage = "<p class='error-msg'>Geçersiz dosya formatı!</p>";
        }
    } else {
        $uploadMessage = "<p class='error-msg'>Dosya yüklenirken bir hata oluştu.</p>";
    }
}

// Dosya silme işlemi
if (isset($_GET['delete_file'])) {
    $file_id_to_delete = $_GET['delete_file'];
    // Dosya bilgilerini almak için veritabanı sorgusu
    $stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ? AND user_id = ?");
    $stmt->execute([$file_id_to_delete, $user_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Sunucudaki dosyayı sil
        if (unlink($file['file_path'])) {
            // Veritabanından dosyayı sil
            $stmt = $pdo->prepare("DELETE FROM files WHERE file_id = ?");
            $stmt->execute([$file_id_to_delete]);
            $uploadMessage = "<p class='success-msg'>Dosya başarıyla silindi.</p>";
        } else {
            $uploadMessage = "<p class='error-msg'>Dosya silinirken bir hata oluştu.</p>";
        }
    }
}

// Dosya paylaşma işlemi
if (isset($_GET['share_file'])) {
    $file_id_to_share = $_GET['share_file'];
    $stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ? AND user_id = ?");
    $stmt->execute([$file_id_to_share, $user_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Paylaşım linkini oluştur
        $shareLink = "http://localhost/finalProject/Interface/uploads/" . basename($file['file_path']);
        $uploadMessage = "<p class='success-msg'>Dosya başarıyla paylaşılabilir: <a href='$shareLink' target='_blank'>$shareLink</a></p>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Yükle</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Temel stil */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .container {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            padding: 40px 50px;
            width: 100%;
            max-width: 600px;
            text-align: center;
            transition: transform 0.3s ease-in-out;
        }

        .container:hover {
            transform: scale(1.03);
        }

        h2 {
            font-size: 30px;
            color: #333;
            margin-bottom: 30px;
        }

        .file-upload-section {
            width: 96%;
            margin-bottom: 20px;
        }

        .file-upload-section input {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 16px;
            background-color: #f0f2f7;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .file-upload-section input:hover {
            border-color: #4CAF50;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .file-list {
            margin-top: 30px;
            text-align: left;
            margin-bottom: 20px;
        }

        .file-list a {
            color: #4CAF50;
            text-decoration: none;
        }

        .file-list a:hover {
            text-decoration: underline;
        }

        .success-msg {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error-msg {
            background-color: #FF5733;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .logout-btn {
            background-color: #f44336;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #d32f2f;
        }

        /* Responsive tasarım */
        @media (max-width: 768px) {
            .container {
                padding: 30px;
            }

            h2 {
                font-size: 26px;
            }

            .file-upload-section input, .btn, .logout-btn {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
    <script>
        function confirmDelete(fileId) {
            if (confirm('Silmek istediğinizden emin misiniz?')) {
                window.location.href = 'upload.php?delete_file=' + fileId;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Hoş geldiniz, <?php echo htmlspecialchars($username); ?>!</h2>

        <!-- Dosya Yükleme Formu -->
        <?php if (isset($uploadMessage)) echo $uploadMessage; ?>
        
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="file-upload-section">
                <input type="file" name="file" required>
            </div>
            <button type="submit" class="btn">Dosya Yükle</button>
        </form>

        <!-- Kullanıcı Dosyaları -->
        <div class="file-list">
            <h3>Yüklediğiniz Dosyalar:</h3>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($files) > 0):
                foreach ($files as $file):
            ?>
                <div>
                    <?php echo htmlspecialchars($file['file_name']); ?> - 
                    <a href="download_file.php?file_id=<?php echo $file['file_id']; ?>">İndir</a> | 
                    <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $file['file_id']; ?>)">Sil</a> | 
                    <a href="upload.php?share_file=<?php echo $file['file_id']; ?>">Paylaş</a>
                </div>
            <?php endforeach; else: ?>
                <p>Henüz dosya yüklemediniz.</p>
            <?php endif; ?>
        </div>

        <!-- Çıkış Butonu -->
        <form action="upload.php" method="POST">
            <button type="submit" name="logout" class="logout-btn">Çıkış Yap</button>
        </form>
    </div>
</body>
</html>