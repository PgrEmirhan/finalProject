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
                echo "<p>Dosya başarıyla yüklendi!</p>";
            } else {
                echo "Dosya yüklenirken bir hata oluştu.";
            }
        } else {
            echo "Geçersiz dosya formatı veya dosya çok büyük!";
        }
    } else {
        echo "Dosya yüklenirken bir hata oluştu.";
    }
}

// AJAX İsteklerini İşleme
if (isset($_POST['action']) && isset($_POST['file_path'])) {
    $file = $_POST['file_path'];

    if ($_POST['action'] == 'delete') {
        if (file_exists($file)) {
            if (unlink($file)) {
                // Veritabanından dosyayı sil
                $stmt = $pdo->prepare("DELETE FROM files WHERE file_path = :file_path");
                $stmt->execute([':file_path' => $file]);

                echo json_encode(["status" => "success", "message" => "Dosya başarıyla silindi."]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Dosya silinemedi!",
                    "file_path" => $file,
                    "error" => error_get_last()
                ]);
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Dosya bulunamadı!",
                "file_path" => $file
            ]);
        }
        exit();
    }

    if ($_POST['action'] == 'share' && file_exists($file)) {
        $share_url = "http://localhost/guest_upload.php?action=download&file=" . urlencode($file);
        echo json_encode(["status" => "success", "share_url" => $share_url]);
        exit();
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
        echo "Dosya bulunamadı!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Misafir Dosya Yükle</title>
    <script>
        function handleAction(action, filePath) {
            var formData = new FormData();
            formData.append("action", action);
            formData.append("file_path", filePath);

            fetch("", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log("Gelen Cevap:", data);
                alert(data.message); // Mesajı göster

                if (action === "delete" && data.status === "success") {
                    location.reload();
                } else if (action === "delete" && data.status === "error") {
                    console.error("Silme Hatası:", data);
                }

                if (action === "share" && data.status === "success") {
                    document.getElementById("shareLink").innerHTML = 'Paylaşım Linki: <a href="' + data.share_url + '" target="_blank">' + data.share_url + '</a>';
                }
            })
            .catch(error => console.error("Hata:", error));
        }
    </script>

    <style>
        /* Genel sayfa ve body stilleri */
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
        }

        h2 {
            font-size: 28px;
            text-align: center;
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
        }

        .file-info a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .file-info a:hover {
            color: #388E3C;
        }

        .file-info button {
            margin: 10px 5px;
            background-color: #F44336;
            transition: background-color 0.3s ease;
        }

        .file-info button:hover {
            background-color: #D32F2F;
        }

        #shareLink a {
            color: #4CAF50;
            text-decoration: none;
        }

        #shareLink a:hover {
            text-decoration: underline;
        }

        .message {
            margin-top: 20px;
            font-size: 18px;
            color: green;
        }

        /* Anasayfaya dön butonu */
        .back-home-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            font-size: 18px;
            color: #4CAF50;
            font-weight: bold;
            transition: color 0.3s ease;
            text-decoration: none;
        }

        .back-home-link:hover {
            color: #388E3C;
        }

        /* Mobil uyumlu tasarım */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            h2 {
                font-size: 24px;
            }

            button {
                padding: 10px 20px;
                font-size: 14px;
            }

            input[type="file"] {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Misafir Olarak Dosya Yükle</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit" name="upload">Dosya Yükle</button>
        </form>

        <?php if ($fileToShow): ?>
            <div class="file-info">
                <h3>Yüklenen Dosya</h3>
                <p><strong>Dosya Adı:</strong> <?php echo htmlspecialchars($fileToShow['file_name']); ?></p>
                <a href="?action=download&file=<?php echo urlencode($fileToShow['file_path']); ?>">İndir</a><br>
                <button onclick="handleAction('share', '<?php echo htmlspecialchars($fileToShow['file_path']); ?>')">Paylaş</button>
                <button onclick="handleAction('delete', '<?php echo htmlspecialchars($fileToShow['file_path']); ?>')">Sil</button>
                <p id="shareLink"></p>
            </div>
        <?php endif; ?>

        <a href="index.php" class="back-home-link">Ana Sayfaya Dön</a>
    </div>

</body>
</html>
