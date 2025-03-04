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
                $fileDestination = __DIR__ . '/uploads/' . uniqid('', true) . '.' . $fileExt;
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

// Dosya silme işlemi
if (isset($_GET['delete'])) {
    $fileId = $_GET['delete'];

    // Dosya bilgilerini veritabanından al
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $filePath = $file['file_path'];
        
        // Veritabanından sil
        $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
        $stmt->execute([$fileId]);

        // Fiziksel dosyayı sil
        if (file_exists($filePath)) {
            unlink($filePath); // Dosyayı sil
            echo "Dosya başarıyla silindi!";
        } else {
            echo "Dosya sistemde bulunamadı.";
        }
    } else {
        echo "Dosya bulunamadı.";
    }
}

// Dosya indirme işlemi
if (isset($_GET['download'])) {
    $fileId = $_GET['download'];

    // Dosya bilgilerini veritabanından al
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $filePath = $file['file_path'];
        
        // Dosyanın var olup olmadığını kontrol et
        if (file_exists($filePath)) {
            // Dosya indir
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            echo "Dosya bulunamadı.";
        }
    } else {
        echo "Dosya bulunamadı.";
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
    <style>
        /* Modal stili */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
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
            <?php
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $fileUrl = 'http://localhost/finalProject/uploads/' . basename($row['file_path']); // Paylaşılabilir link
                    echo "<li>";
                    $fileExt = strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));
                    // Eğer dosya bir resimse, resim olarak göster
                    if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo "<img src='$fileUrl' width='100' height='100'>";
                    }
                    // Eğer dosya bir video ise, video olarak göster
                    if (in_array($fileExt, ['mp4', 'avi'])) {
                        echo "<video width='200' controls><source src='$fileUrl' type='video/mp4'></video>";
                    }
                    echo "<br>";
                    echo $row['file_name'];
                    echo " <a href='?download=" . $row['id'] . "'>İndir</a> | "; // İndirme linki
                    echo "<a href='?delete=" . $row['id'] . "' onclick='return confirm(\"Dosyayı silmek istediğinize emin misiniz?\")'>Sil</a> | "; // Silme linki
                    echo "<button class='share-btn' data-link='$fileUrl'>Paylaş</button>"; // Paylaş butonu
                    echo "</li>";
                }
            ?>
        </ul>
    </div>

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Paylaşılabilir Link</h2>
            <input type="text" id="shareLink" readonly>
            <button id="copyLinkBtn">Kopyala</button>
        </div>
    </div>

    <script>
        // Modal penceresini kontrol et
        var modal = document.getElementById("myModal");
        var shareButtons = document.querySelectorAll(".share-btn");
        var shareLinkInput = document.getElementById("shareLink");
        var closeModal = document.getElementsByClassName("close")[0];
        var copyLinkBtn = document.getElementById("copyLinkBtn");

        // Paylaş butonlarına tıklanmasıyla modal açılması
        shareButtons.forEach(button => {
            button.addEventListener("click", function() {
                var link = this.getAttribute("data-link");
                shareLinkInput.value = link;  // Linki modalda göster
                modal.style.display = "block";  // Modalı göster
            });
        });

        // Modalı kapatma işlemi
        closeModal.onclick = function() {
            modal.style.display = "none";
        }

        // Modal dışına tıklanırsa kapatma işlemi
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Kopyalama işlemi
        copyLinkBtn.addEventListener('click', function() {
            shareLinkInput.select();
            document.execCommand('copy');
            alert('Link kopyalandı!');
        });
    </script>
</body>
</html>
