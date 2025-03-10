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
                $fileToShow = [
                    'file_name' => $fileName,
                    'file_path' => $fileDestination
                ];
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
                alert(data.message);
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
</head>
<body>
    <h2>Misafir Olarak Dosya Yükle</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required><br><br>
        <button type="submit" name="upload">Dosya Yükle</button>
    </form>
    <hr>

    <?php if ($fileToShow): ?>
        <h3>Yüklenen Dosya</h3>
        <strong><?php echo htmlspecialchars($fileToShow['file_name']); ?></strong><br>
        <a href="?action=download&file=<?php echo urlencode($fileToShow['file_path']); ?>">İndir</a>
        <button onclick="handleAction('share', '<?php echo htmlspecialchars($fileToShow['file_path']); ?>')">Paylaş</button>
        <button onclick="handleAction('delete', '<?php echo htmlspecialchars($fileToShow['file_path']); ?>')">Sil</button>
        <p id="shareLink"></p>
        <hr>
    <?php endif; ?>

    <a href="index.php">Ana Sayfaya Dön</a>
</body>
</html>
