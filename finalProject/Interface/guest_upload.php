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

// Misafir kullanıcı kontrolü
$is_guest = 1; // Misafir olarak kabul edilmekte (is_guest = 1)
if (isset($_SESSION['user_id'])) {
    $is_guest = 0;
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = null; // Misafir kullanıcıda user_id null olacak
}

// Dosya yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileError = $_FILES['file']['error'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'docx', 'zip');

    if ($fileError === 0) {
        if (in_array($fileExt, $allowed)) {
            if ($fileSize < 10000000) { 
                $fileDestination = __DIR__ . '/uploads/' . uniqid('', true) . '.' . $fileExt;
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Dosya başarıyla yüklendi
                    $expiryTime = time(); // Yükleme zamanı
                    // Veritabanına kaydetme
                    $stmt = $pdo->prepare("INSERT INTO files (file_name, file_path, expiry_time, user_id, is_guest) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$fileName, $fileDestination, $expiryTime, $user_id, $is_guest]);
                    $uploadMessage = "<p class='success-msg'>Dosya başarıyla yüklendi.</p>";
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

    // Dosyanın veritabanında var olup olmadığını kontrol et
    $stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ? AND (user_id = ? OR is_guest = 1)");
    $stmt->execute([$file_id_to_delete, $user_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Dosya sisteminden silme işlemi
        if (unlink($file['file_path'])) {
            // Veritabanından dosya kaydını silme
            $stmt = $pdo->prepare("DELETE FROM files WHERE file_id = ?");
            $stmt->execute([$file_id_to_delete]);
            $uploadMessage = "<p class='success-msg'>Dosya başarıyla silindi.</p>";
        } else {
            $uploadMessage = "<p class='error-msg'>Dosya silinirken bir hata oluştu.</p>";
        }
    } else {
        $uploadMessage = "<p class='error-msg'>Dosya bulunamadı veya yetkiniz yok.</p>";
    }
}

// Dosya paylaşma işlemi
if (isset($_GET['share_file'])) {
    $file_id_to_share = $_GET['share_file'];
    
    // Dosyanın veritabanında var olup olmadığını kontrol et
    $stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ? AND (user_id = ? OR is_guest = 1)");
    $stmt->execute([$file_id_to_share, $user_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Paylaşılabilir link oluşturuluyor
        $shareLink = "http://localhost/finalProject/Interface/uploads/" . basename($file['file_path']);
        $uploadMessage = "<p class='success-msg'>Dosya başarıyla paylaşılabilir: <a href='$shareLink' target='_blank'>$shareLink</a></p>";
    } else {
        $uploadMessage = "<p class='error-msg'>Dosya bulunamadı veya yetkiniz yok.</p>";
    }
}

// Dosyaları listeleme
try {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? OR is_guest = 1");
    $stmt->execute([$user_id]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
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
            display: none;
        }

        .file-upload-section input:hover {
            border-color: #4CAF50;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            font-size: 15px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration:none;
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
            overflow: hidden;
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

        #drop-area {
            width: 92%;
            border: 2px dashed #ccc;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            cursor: pointer;
        }

        #drop-area.hover {
            background-color: #f0f0f0;
        }

        #progress-container {
            width: 98%;
            height: 10px;
            padding-top:10px;
            padding-bottom:10px;
            padding-left:10px;
            background-color: #f0f0f0;
            margin-top: 10px;
            border-radius: 5px;
        }

        #progress-bar {
            width: 0;
            height: 100%;
            background-color: #4CAF50;
            text-align: center;
            color: white;
            line-height: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hoş geldiniz, Misafir!</h2>

        <?php if (isset($uploadMessage)) echo $uploadMessage; ?>

        <div id="drop-area" onclick="triggerFileInput()">
            Dosya Buraya Sürükleyin veya Seçmek için Tıklayın
        </div>

        <div id="progress-container">
            <div id="progress-bar">0%</div>
        </div>
        <br>

        <form id="uploadForm" action="guest_upload.php" method="POST" enctype="multipart/form-data" style="display:none;">
            <input type="file" name="file" id="fileInput" />
        </form>

        <button class="btn" onclick="uploadFile()">Dosya Yükle</button>

        <div class="file-list">
            <h3>Yüklenen Dosyalar:</h3>
            <?php
                foreach ($files as $file) {
                    // Dosyanın yolu
                    $filePath = 'uploads/' . basename($file['file_path']);
                    echo '<p>' . $file['file_name'] . ' - ';
                    echo '<a href="#" onclick="confirmDelete(' . $file['file_id'] . ')">Sil</a> | ';
                    echo '<a href="?share_file=' . $file['file_id'] . '">Paylaş</a> | ';
                    echo '<a href="' . $filePath . '" download >İndir</a></p>';
                }
            ?>
        </div>

        <a href="index.php" class="btn">Ana Sayfaya Dön</a>
    </div>

    <script>
        function triggerFileInput() {
            document.getElementById('fileInput').click();
        }

        function uploadFile() {
            var fileInput = document.getElementById('fileInput');
            var file = fileInput.files[0];
            var formData = new FormData();
            formData.append('file', file);
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'guest_upload.php', true);
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    var percent = (e.loaded / e.total) * 100;
                    document.getElementById('progress-bar').style.width = percent + '%';
                    document.getElementById('progress-bar').textContent = Math.round(percent) + '%';
                }
            });

            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert("Dosya başarıyla yüklendi!");
                    location.reload();
                } else {
                    alert("Dosya yükleme başarısız!");
                }
            };

            xhr.send(formData);
        }

        var dropArea = document.getElementById('drop-area');
        dropArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropArea.classList.add('hover');
        });

        dropArea.addEventListener('dragleave', function() {
            dropArea.classList.remove('hover');
        });

        dropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            dropArea.classList.remove('hover');
            var file = e.dataTransfer.files[0];
            document.getElementById('fileInput').files = e.dataTransfer.files;
        });
        
        function confirmDelete(fileId) {
            var confirmation = confirm("Bu dosyayı silmek istediğinizden emin misiniz?");
            if (confirmation) {
                window.location.href = "?delete_file=" + fileId;
            }
        }
    </script>
</body>
</html>
