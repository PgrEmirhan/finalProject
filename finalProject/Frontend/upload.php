<?php
session_start();

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Çıkış işlemi
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
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
$username = $_SESSION['user_name'];

// Dosya yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileError = $_FILES['file']['error'];

    $uploadMessage = '';

    if ($fileError === 0) {
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'docx', 'zip');

        if (in_array($fileExt, $allowed)) {
            if ($fileSize < 10000000) { 
                $fileDestination = __DIR__ . '/uploads/' . uniqid('', true) . '.' . $fileExt;
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
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
    $stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ? AND user_id = ?");
    $stmt->execute([$file_id_to_delete, $user_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        if (unlink($file['file_path'])) {
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
        $shareLink = "http://localhost/finalProject/Frontend/uploads/" . basename($file['file_path']);
        $uploadMessage = "<p class='success-msg'>Dosya başarıyla paylaşılabilir: <a href='$shareLink' target='_blank'>$shareLink</a></p>";
    }
}
?><!DOCTYPE html>
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
            display: none; /* Dosya inputu gizli olacak */
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

        /* Yükleme barı ve Drag & Drop stil */
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
        <h2>Hoş geldiniz, <?php echo htmlspecialchars($username); ?>!</h2>

        <!-- Dosya Yükleme Formu -->
        <?php if (isset($uploadMessage)) echo $uploadMessage; ?>
        
        <!-- Drag and Drop alanı -->
        <div id="drop-area" onclick="triggerFileInput()">
            Dosya Buraya Sürükleyin veya Seçmek için Tıklayın
        </div>

        <!-- Yükleme Barı -->
        <div id="progress-container">
            <div id="progress-bar">0%</div>
        </div>
<br>
        <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data" style="display:none;">
            <input type="file" name="file" id="fileInput" />
        </form>

        <button class="btn" onclick="uploadFile()">Dosya Yükle</button>

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
                    <?php echo htmlspecialchars($file['file_name']);         
                    $filePath = 'uploads/' . basename($file['file_path']); 
        echo htmlspecialchars($file['file_name']); ?> - 
                    <a href="<?php echo $filePath; ?>" download>İndir</a> | 
                    <a href="upload.php?delete_file=<?php echo $file['file_id']; ?>" onclick="return confirm('Bu dosyayı silmek istediğinizden emin misiniz?');">Sil</a> | 
                    <a href="upload.php?share_file=<?php echo $file['file_id']; ?>">Paylaş</a>
                </div>
            <?php endforeach; else: ?>
                <p>Henüz dosya yüklemediniz.</p>
            <?php endif; ?>
        </div>

        <form action="upload.php" method="POST">
            <button type="submit" name="logout" class="logout-btn">Çıkış Yap</button>
        </form>
    </div>

    <script>
        var dropArea = document.getElementById('drop-area');
        var fileInput = document.getElementById('fileInput');
        var progressBar = document.getElementById('progress-bar');
        var filesToUpload = []; // Dosyalar burada tutulacak

        // Tıklama ile dosya seçim penceresini açma
        function triggerFileInput() {
            fileInput.click(); // input dosya seçme penceresini açar
        }

        // Dragover, dosya bırakılması için olay dinleyicileri
        dropArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropArea.classList.add('hover');
        });

        dropArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropArea.classList.remove('hover');
        });

        dropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            dropArea.classList.remove('hover');
            var files = e.dataTransfer.files;
            handleFileSelection(files);
        });

        // Dosya inputu için değişim olayı
        fileInput.addEventListener('change', function(e) {
            var files = e.target.files;
            handleFileSelection(files);
        });

        // Dosya seçimi yapıldığında dosyaları listele
        function handleFileSelection(files) {
            filesToUpload = files;
            // Dosya seçildiğinde yükleme butonu aktif olsun
            document.querySelector('.btn').disabled = false;
        }

        // Yükleme butonuna tıklanarak dosya yükleme işlemi
        function uploadFile() {
            if (filesToUpload.length === 0) {
                alert("Lütfen bir dosya seçin.");
                return;
            }

            var formData = new FormData();
            formData.append("file", filesToUpload[0]);

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "upload.php", true);

            xhr.upload.addEventListener("progress", updateProgressBar, false);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Yükleme tamamlandığında mesajı göster
                    progressBar.style.width = '100%';
                    progressBar.innerHTML = 'Yükleme Tamamlandı!';
                    setTimeout(function() {
                        alert("Dosya başarıyla yüklendi!");
                        location.reload();
                    }, 1000); // 1 saniye sonra sayfa yenilensin
                } else {
                    alert("Dosya yüklenirken hata oluştu.");
                }
            };

            xhr.send(formData);
        }

        // Yükleme ilerlemesi barı
        function updateProgressBar(event) {
            var percentage = (event.loaded / event.total) * 100;
            progressBar.style.width = percentage + '%';
            progressBar.innerHTML = Math.round(percentage) + '%';
        }
    </script>
</body>
</html>
