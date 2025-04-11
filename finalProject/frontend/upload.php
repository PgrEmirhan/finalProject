<?php
session_start();
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
} 
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
 
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

                 $expiryTime = '0000-00-00 00:00:00';  

                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    $sql = "INSERT INTO files (file_name, file_path, user_id, expiry_time) VALUES (?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$fileName, $fileDestination, $user_id, $expiryTime]);

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
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Yükle</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
body{
      font-family: 'Inter', sans-serif;   
      overflow-x: hidden; 
      color: black;

    } 
    .nav-container{ 
      display: flex;
      justify-content: space-around;
      align-items: center;
      background-color: rgb(255, 255, 255);
      color: white;
      width: 100%; 
      font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
      top: 0;
      left: 0;    
      position: fixed;
      z-index: 1;
    }
    .nav-container ul{
      margin-left: 956px;
      list-style-type: none; 
      display:flex;
      gap: 15px; 
   }
    .nav-container ul a{ 
      text-decoration: none;
      color: rgb(0, 0, 0); 
      padding: 10px; 
      font-size: 18px;
      transition: all 0.7s;
      background-color: rgb(255, 255, 255);
   } 
main{
    margin-top: 80px;
    margin-bottom: 30px;
} 
.upload{
  display: flex;
  flex-direction: column;
  justify-content: center; 
  align-items: center;
  margin-top: -15px;
}
img{
    margin-top: -7px;
}
.upload-file{
  padding: 15px;
  border: 2px dashed;
  margin-bottom: 15px;
  cursor: pointer;
}
    footer {
    background-color: black;
    color: white;
    width: 100%;  
    padding: 10px;
    position: absolute;  
    bottom: auto;  
    left: 0;
    right: 0;  
    text-align: center; 
    font-family: 'Inter', sans-serif;
    }

    footer a{
    text-decoration: none;
    color: white;
    font-weight: bold;

    }
    .footer-nav{
    display: flex;
    justify-content: space-around;

    }
    .footer-nav ul{  list-style-type: none;
    }

        #progress-container {
            width: 30.5%;
            height: 10px;
            padding-top:10px;
            padding-bottom:10px;
            padding-left:10px;
            background-color: lightgreen;
            margin-top: 20px;
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
        .upload-btn{
          width: 31.5%;
          padding: 15px 5px;
          border: 1px solid;
          border-radius: 5px;
          background: linear-gradient(15deg, rgb(205, 248, 205), lightyellow, rgb(227, 246, 252),rgb(182, 184, 187), rgb(235, 220, 222));
          cursor: pointer;
          font-weight: bolder;
          font-family: 'Inter', sans-serif;
          font-size: 15px;
        }
        #drop-area{
            border: 2px dashed;
            padding: 8px;
            width: 395px;
            text-align:center;
        }
        .logout-btn{
            margin-top: 10px;
            border: 1px solid black;
            border-radius: 5px;
            color: red; 
            cursor: pointer;
            padding: 10px 45px;
            transition: all 0.5s;
            font-weight: 650; 
        }
        .logout-btn:hover{  
            color: white;
            background-color: red;  
        }
        .file-list {
            margin-top: 20px;
        }
        .file-list p {
            margin: 10px 0;
        }
        .file-list a {
            margin-right: 10px;
            text-decoration: none;
            color: #4CAF50;
        }   
    </style>
</head>
<body>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <header>
    <nav class="nav-container">
      
    <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px; margin-top:3px;"></a>
      <ul>  
        <li><a href="contact.php">         
           <i class="fas fa-envelope icon"></i>
          İletişim</a></li>
      </ul>
    </nav>
  </header>
  <main>
    <div class="upload">
        <h2 style="font-size: 25px; ">Hoş geldiniz, <?php echo htmlspecialchars($username); ?>!</h2>   
           <img src="images/upload.png" alt="" width="240"><br>
 
        <?php if (isset($uploadMessage)) echo $uploadMessage; ?>
         
        <div id="drop-area" onclick="triggerFileInput()">
            Dosya Buraya Sürükleyin veya Seçmek için Tıklayın
        </div>
 
        <div id="progress-container">
            <div id="progress-bar">0%</div>
        </div>
        <br>

        <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data" style="display:none;">
            <input type="file" name="file" id="fileInput" />
        </form>

        <button class="upload-btn" onclick="uploadFile()">Dosya Yükle</button>

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
                    <a href="uploads/<?php echo basename($file['file_path']); ?>" download>İndir</a> | 
                    <a href="upload.php?delete_file=<?php echo $file['file_id']; ?>" onclick="return confirm('Bu dosyayı silmek istediğinizden emin misiniz?');">Sil</a> | 
                    <a href="upload.php?share_file= <?php echo $file['file_id']; ?>">Paylaş</a>
                </div>
            <?php endforeach; else: ?>
                <p>Henüz dosya yüklemediniz.</p>
            <?php endif; ?>
        </div>

        <form action="upload.php" method="POST">
            <button type="submit" name="logout" class="logout-btn">Çıkış Yap</button>
        </form>
    </div>
    </main>
    <footer>  
    <div class="footer-nav"> 
        <ul>
        <a href="#"><h3>HIZLI BAĞLANTILAR</h3></a>
        <li><a href="index.php">Anasayfa</a></li> 
        <li><a href="register.php">Üye ol</a></li>  
        <li><a href="contact.php">İletişim</a></li>
        </ul>
        <ul>
        <a href="#"><h3>YASAL BİLGİLER</h3></a>
        <li><a href="">Kullanım Koşulları </a></li>
        <li><a href="">Gizlilik Politikası </a></li>
        <li><a href="">Çerez Politikası</a></li> 
        </ul>
        <ul>
        <a href="#"><h3>SOSYAL MEDYA</h3></a>
        <li><a href="">Facebook </a></li>
        <li><a href="">Twitter</a></li>
        <li><a href="">Instagram</a></li>
        <li><a href="">LinkedIn</a></li>
        </ul>
        <ul>
        <a href="#"><h3>İLETİŞİM BİLGİLERİ </h3></a>
        <li><a href=""><b>Telefon: </b> +90 123 456 789
        </a></li>
        <li><a href=""><b>Email: </b>support@dosyapaylasim.com
        </a></li> 
        </ul>
    </div> 
        <p align="center">Tüm haklar saklıdır. TE-FS &copy2025</p>
    </footer>
    <script>   
function triggerFileInput() {
    document.getElementById('fileInput').click(); 
}
 
var dropArea = document.getElementById('drop-area');
var fileInput = document.getElementById('fileInput');
var progressBar = document.getElementById('progress-bar');
var filesToUpload = []; 
 
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
 
fileInput.addEventListener('change', function(e) {
    var files = e.target.files;
    handleFileSelection(files);
});
 
function handleFileSelection(files) {
    filesToUpload = files; 
    document.querySelector('.upload-btn').disabled = false;
}
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
            progressBar.style.width = '100%';
            progressBar.innerHTML = 'Yükleme Tamamlandı!';
            setTimeout(function() {
                alert("Dosya başarıyla yüklendi!");
                location.reload(); 
            }, 1000);  
        } else {
            alert("Dosya yüklenirken hata oluştu.");
        }
    };

    xhr.send(formData);
}

 
function updateProgressBar(event) {
    var percentage = (event.loaded / event.total) * 100;
    progressBar.style.width = percentage + '%';
    progressBar.innerHTML = Math.round(percentage) + '%';
}

    </script>
</body>
</html>
