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

$query = $pdo->prepare("SELECT * FROM users WHERE user_name = ?");
$query->execute([$username]);
$user = $query->fetch();

$membership = $user['membership_type']; // Üyelik türünü alıyoruz
$sql = "SELECT * FROM files WHERE user_id = ?";
$params = [$user_id];

if ($membership != 'free') {
    if (!empty($_GET['type'])) {
        $sql .= " AND file_name LIKE ?";
        $params[] = "%." . ltrim($_GET['type'], '.'); // baştaki noktayı temizle
    }

    if (!empty($_GET['min_size']) && is_numeric($_GET['min_size'])) {
        $sql .= " AND file_size >= ?";
        $params[] = (int)$_GET['min_size'];
    }

    if (!empty($_GET['max_size']) && is_numeric($_GET['max_size'])) {
        $sql .= " AND file_size <= ?";
        $params[] = (int)$_GET['max_size'];
    }
}

// Kullanıcının şu ana kadar yüklediği toplam dosya boyutunu al
$stmt = $pdo->prepare("SELECT SUM(file_size) AS total_size FROM files WHERE user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalUsed = $result['total_size'] ?? 0; // Bayt cinsinden
// Kota sınırlarını byte cinsinden belirleyelim
$maxQuota = 0;
$maxFileSize = 0;

switch ($membership) {
    case 'free':
        $maxQuota = 10 * 1024 * 1024; // 10 MB
        $maxFileSize = 10 * 1024 * 1024;
        break;
    case 'monthly':
        $maxQuota = 1024 * 1024 * 1024; // 1 GB
        $maxFileSize = 200 * 1024 * 1024; // örnek: 200 MB dosya sınırı
        break;
    case 'yearly':
        $maxQuota = 5 * 1024 * 1024 * 1024; // 5 GB
        $maxFileSize = 500 * 1024 * 1024; // örnek: 500 MB dosya sınırı
        break;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileSize = $file['size'];

    // KOTA KONTROLÜ
    if (($totalUsed + $fileSize) > $maxQuota) {
        $uploadMessage = "<p class='error-msg'>Yükleme sınırınızı aştınız. Üyeliğinize uygun maksimum kapasiteyi doldurdunuz.</p>";
    } elseif ($fileSize > $maxFileSize) {
        $uploadMessage = "<p class='error-msg'>Bu dosya üyelik türünüz için çok büyük. Maksimum izin verilen dosya boyutu: " . round($maxFileSize / 1024 / 1024) . " MB</p>";
    } else {
        // Devam et: burada dosya taşınması, veritabanına yazılması vb. işlemler olur
        // Şu anki mevcut dosya yükleme kodlarını buraya yerleştirirsiniz
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    // Dosya ismi ve yolu oluşturuluyor
                    $fileNewName = uniqid('', true) . '.' . $fileExt;
                    $fileDestination = __DIR__ . '/uploads/' . $fileNewName;

                    $expiryTime = '0000-00-00 00:00:00';  // İhtiyaca göre düzenlenebilir

                    try {
                        if (move_uploaded_file($fileTmpName, $fileDestination)) {
                            // Veritabanına dosya bilgisi ekleniyor
                            $sql = "INSERT INTO files (file_name, file_path, user_id, expiry_time,file_size) VALUES (?, ?, ?, ?,?)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$fileName, $fileDestination, $user_id, $expiryTime, $fileSize]);

                            $uploadMessage = "<p class='success-msg'>Dosya başarıyla yüklendi!</p>";
                        } else {
                            $uploadMessage = "<p class='error-msg'>Dosya yüklenirken bir hata oluştu.</p>";
                        }
                    } catch (Exception $e) {
                        $uploadMessage = "<p class='error-msg'>Veritabanı hatası: " . $e->getMessage() . "</p>";
                    }

                } else {
                    $uploadMessage = "<p class='error-msg'>Dosya çok büyük, lütfen 10MB'dan küçük bir dosya yükleyin.</p>";
                }
            } else {
                $uploadMessage = "<p class='error-msg'>Geçersiz dosya formatı! Lütfen desteklenen formatlardan birini seçin.</p>";
            }
        } else {
            $uploadMessage = "<p class='error-msg'>Dosya yüklenirken bir hata oluştu. Lütfen tekrar deneyin.</p>";
        }
    }

if ($membership != 'free' && count($files) > 0) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['files'])) {
        $files = $_POST['files'];

        $zip = new ZipArchive();
        $zipName = 'arsiv_' . time() . '.zip';
        $zipPath = 'uploads/' . $zipName;

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                $filePath = realpath($file);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($filePath));
                }
            }
            $zip->close();

            // İndirme başlat
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($zipPath) . '"');
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);

            // Geçici ZIP silinsin
            unlink($zipPath);
            exit;
        } else {
            echo "ZIP oluşturulamadı.";
        }
    } else {
        echo "Hiç dosya seçilmedi.";
    }
} 

// Dosya silme işlemi
if (isset($_GET['delete_file'])) {
    $fileId = $_GET['delete_file'];
    $userId = $_SESSION['user_id']; // Giriş yapmış kullanıcının ID'si

    // Veritabanından dosya bilgilerini al
    $stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ? AND user_id = ?");
    $stmt->execute([$fileId, $userId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Dosyanın fiziksel yolunu al
        $filePath = $file['file_path'];

        // Veritabanından dosyayı sil
        $stmt = $pdo->prepare("DELETE FROM files WHERE file_id = ?");
        $stmt->execute([$fileId]);

        // Dosyayı fiziksel olarak sil
        if (file_exists($filePath)) {
            unlink($filePath); // Dosyayı sil
            $uploadMessage = "<p class='success-msg'>Dosya başarıyla silindi!</p>";
        } else {
            $uploadMessage = "<p class='error-msg'>Dosya sistemde bulunamadı.</p>";
        }
    } else {
        $uploadMessage = "<p class='error-msg'>Dosya bulunamadı veya yetkiniz yok.</p>";
    }
}


    
        if (isset($_GET['share_file'])) {
            $file_id_to_share = $_GET['share_file'];

            try {
                $stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ? AND user_id = ?");
                $stmt->execute([$file_id_to_share, $user_id]);
                $file = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($file) {
                    $shareLink = "http://localhost/finalProject/frontend/uploads/" . basename($file['file_path']);
                    $uploadMessage = "<p class='success-msg'>Dosya başarıyla paylaşılabilir: <a href='$shareLink' target='_blank'>$shareLink</a></p>";
                } else {
                    $uploadMessage = "<p class='error-msg'>Dosya bulunamadı veya yetkiniz yok.</p>";
                }
            } catch (Exception $e) {
                $uploadMessage = "<p class='error-msg'>Veritabanı hatası: " . $e->getMessage() . "</p>";
            }
        }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Alınan veriler
        $recipientEmail = $_POST['recipient'];
        $fileLink = $_POST['file_link'];

        // Dosya adı ve gönderim mesajı
        $subject = "Paylaşılan Dosya Linki";
        $message = "Merhaba,\n\nBu mesaj, bir dosya paylaşımı içermektedir. Aşağıdaki linkten dosyayı indirebilirsiniz:\n\n" . $fileLink . "\n\nİyi günler.";

        // E-posta başlıkları
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type:text/plain;charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@domain.com" . "\r\n"; // Gönderen e-posta adresi

        // E-posta gönderme
        if (mail($recipientEmail, $subject, $message, $headers)) {
            echo "Dosya başarıyla paylaşıldı!";
        } else {
            echo "E-posta gönderilemedi.";
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
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    <style>
    body{
        font-family: 'Inter', sans-serif;   
        overflow-x: hidden; 
        color: black;

        }       
        
  body.dark-mode{
    background-color: black;
    color: rgb(255, 255, 255);
  }
  body.dark-mode header{
    background-color: black;
    color: rgb(255, 255, 255);
    border-bottom: 2px solid white;
  }
  
  body.dark-mode .nav-container{
    background-color: black;
    color: rgb(255, 255, 255);
  }
  body.dark-mode .nav-container a{
    background-color: black;
    color: rgb(255, 255, 255);
  } 
  body.dark-mode .nav-container .logo{ 
    color: rgb(255, 255, 255);
  }
  body.dark-mode .nav-container .fa-solid{ 
    color: rgb(255, 255, 255);
  }
  body.dark-mode main{ 
    background-color: black;
  }
  body.dark-mode footer{ 
    background-color: black;
    color: white;
    border: 1px solid white;
  } 
  body.dark-mode footer i { 
    color: white;
  } 
  body.dark-mode footer span{ 
    color: white;
  } 
  body.dark-mode .hero-section{
    background-color: black;
  }
  body.dark-mode   #dark-mode-toggle{
    width: 2rem;
    height: 2rem;
    border: 1px solid white;
    border-radius: 100%;
    font-size: 1.3rem;
    background-color: transparent;
    cursor: pointer;
  }
  #dark-mode-toggle{
    width: 2rem;
    height: 2rem;
    border: 1px solid;
    border-radius: 100%;
    font-size: 1.3rem;
    background-color: transparent;
    cursor: pointer;
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
        font-family: 'Inter',sans-serif;
        }

        footer a{
        text-decoration: none;
        color: white;

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
                padding: 10px 95px;
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
            .satin-btn{ 
            border: none;
            padding: 15px 45px;
            background-color: lightgoldenrodyellow;
            border-radius: 15px;
            font-size: 15px; 
            cursor: pointer;
            font-weight: 650;
            font-family: 'Inter', sans-serif;   
        }
    .premium-price {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 40px;
            } 

            .price-card {
            background-color: #fff;  
            padding: 5px 20px; 
            width: 100%;  
            transition: all 0.3s ease;  
            text-align: center;
            border: 1px solid #000000;   
            }

            .premium-price .price-card h4 { 
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            }

            .premium-price .price-card ul {
            list-style-type: none;
            padding: 0;
            font-size: 14px;
            color:rgb(92, 92, 92); 
            }

            .price-card ul li {
            margin-bottom: 8px;
            }
        
            .premium-price.price-card:hover { 
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);  
            } 
        
            .price-card:nth-child(1) {
            background-color:rgb(255, 255, 255);  
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);  
            color: black;
            } 
            .price-card:nth-child(1) ul{    
                margin-top:  35px;
            }
            .price-card:nth-child(1) button{    
                margin-top: 10px;
                background-color: #66bcf1;  
                color:
            }
            .price-card:nth-child(2) {
            background-color:rgb(255, 255, 255);  
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);  
            color: black;
            } 
            .price-card:nth-child(2) ul{    
                margin-top:  35px;
            }
            .price-card:nth-child(2) button{    
                margin-top: 10px;
                background-color: #66bcf1;  
            } 
            
            .price-card:nth-child(3) ul{    
                margin-top: 23px;
                margin-bottom: 10px;
            }
            .price-card:nth-child(3) button{    
                margin-top: 12px; 
            }

                .premium-container h2{
                    margin-top: 55px;
                }    
                /* Arka Plan (Overlay) */
    #overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px); /* Arka plan bulanıklığı */
        z-index: 1000;
    }

    /* Modal Stili */
    #shareModal {
        display: none;
        position: fixed;
        top: 20%;
        left: 35%;
        width: 30%;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 1001;
        transition: all 0.3s ease;
    }
    .archive-btn{
        display: flex;
        margin: 0 auto;
    }
    </style>
</head>
<body>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <header>
    <nav class="nav-container">
      
    <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px; margin-top:0px; margin-right: 111px;"></a>
      <ul>  
        <li><a href="contact.php" style="margin-right: 1px;">         
           <i class="fas fa-envelope icon"></i>
          İletişim</a></li>
      </ul>
           <button id="dark-mode-toggle"> 
         <i class="fa-solid fa-moon"></i>
      </button>
    </nav>
  </header>
  <main>
    <div class="upload">
        <h2 style="font-size: 25px; ">Hoş geldiniz, <?php echo htmlspecialchars($username); ?>!</h2>   
           <img src="images/file-upload.png" alt="" width="240"><br>
 
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
        <?php if ($membership!=='free'):?>
        <form id="filterForm" method="GET" action="upload.php">
        <h3>Filtreleme</h3>
        <label>Tür (uzantı, örn: txt, pdf):</label>
        <input type="text" name="type" value="<?= htmlspecialchars($_GET['type'] ?? '') ?>">
        <br>
        <label>Minimum Boyut (bayt):</label>
        <input type="number" name="min_size" value="<?= htmlspecialchars($_GET['min_size'] ?? '') ?>">
        <br>
        <label>Maksimum Boyut (bayt):</label>
        <input type="number" name="max_size" value="<?= htmlspecialchars($_GET['max_size'] ?? '') ?>">
        <br>
        <input type="submit" value="Filtrele">
        <button id="showAllBtn">Hepsini Getir</button>
    </form>
    <?php endif; ?>



        <div class="file-list">
            <h3>Yüklediğiniz Dosyalar:</h3>
          <form id="archiveForm" method="POST" action="archive.php">
    <?php if (count($files) > 0): ?>
        <?php foreach ($files as $file): ?>
            <div>
                <?php if($membership!=='free'):?>
                <input type="checkbox" name="files[]" value="<?= htmlspecialchars($file['file_path']) ?>">
                <?php endif;?>
                <?= htmlspecialchars($file['file_name']) ?> (<?= $file['file_size'] ?> bayt)          
                <a href="uploads/<?= basename($file['file_path']) ?>" download>İndir</a> | 
                <a href="upload.php?delete_file=<?= $file['file_id'] ?>" onclick="return confirm('Bu dosyayı silmek istediğinizden emin misiniz?');">Sil</a> | 
                <a href="#" onclick="openShareModal('<?= addslashes(htmlspecialchars(basename($file['file_path']))) ?>'); return false;">Paylaş</a> 
            </div>
        <?php endforeach; ?>
        <br>
        <?php if ($membership!=='free'): ?>
        <button type="submit" class='archive-btn'>Seçilenleri Arşivle</button>
        <?php endif; ?>
    <?php else: ?>
        <p>Henüz dosya yüklemediniz.</p>
    <?php endif; ?>
</form>

        </div>
<!-- Modal -->
 <!-- Arka plan bulanıklaştırma için Overlay -->
<div id="overlay"></div>
<div id="shareModal" style="display:none; position:fixed; top:20%; left:35%; width:30%; background:white; padding:20px; border:1px solid #ccc; z-index:1000;">
    <h3>Paylaşım Ayarları</h3>
    <form id="shareForm" action="shareFile.php" method="POST">
        <div class="form-group">
<!-- Paylaşım Türü -->
<label for="shareType">Paylaşım Türü:</label>
<select id="shareType" name="shareType" required onchange="toggleShareOptions()">
    <option value="public">Genel</option>
    <option value="private">Özel</option>
</select>
        </div>

        <!-- Hidden input for file ID -->
        <input type="hidden" name="file_id" id="modalFileId">

<!-- Parola (sadece özel paylaşımda gösterilecek) -->
<div id="passwordField" style="display: none;">
    <label>Parola (isteğe bağlı):</label><br>
    <input type="text" name="password"><br><br>
</div>

<!-- Geçerlilik Süresi -->
        <label>Geçerlilik süresi (gün):</label><br>
        <input type="number" name="expiry_days" min="1" value="7"><br><br>

        <!-- Max indirme sayısı -->
        <label>Max indirme sayısı (isteğe bağlı):</label><br>
        <input type="number" name="max_downloads" min="1"><br><br>

        <!-- Paylaşılacak Link -->
        <label>Paylaşım Linki:</label><br>
        <input type="text" id="shareLink" name="shareLink" placeholder="Paylaşılacak dosya linki" required readonly />
        <button type="button" id="copyBtn" onclick="copyLink()">Kopyala</button><br><br>

<!-- Her zaman görünecek -->
<label>Paylaşılacak Kişinin E-Postası:</label><br>
<input type="email" id="recipient" name="recipient" placeholder="Kullanıcı e-posta adresi" required><br><br>

        <!-- Submit ve İptal Butonları -->
        <button onclick="openShareModal(
    '<?php echo basename($file['file_path']); ?>',
    '<?php echo $file['file_id']; ?>'
    )">Paylaş</button>
        <button type="button" onclick="closeModal()">İptal</button>
    </form>
</div>

<form action="upload.php" method="POST">
        <button type="submit" name="logout" class="logout-btn">Çıkış Yap</button>
        </form>
    </div>

    <div class="premium-container">
  <h2 align="center" style="font-size: 32px;">
    <i class="fa-solid fa-money-bill"></i>
    Üyelik Planları
     <i class="fa-solid fa-money-bill"></i>
  </h2> 
  <div class="premium-price">
    <div class="price-card"> 
      <h3 style="font-size: 32px;"> Aylık Üyelik </h3>   
        <h4 style="font-size: 34px; width: 100%; color: black; height: 30px; ">Fiyat: 199,99 TL</h4> 
      <br>
      <ul>
      <li><i class="fa-solid fa-check"></i> 15GB Bulut depolama alanı </li>
        <li><i class="fa-solid fa-check"></i> Gelişmiş dosya arşivleme ve filtreleme</li>
        <li><i class="fa-solid fa-check"></i> 1 GB'a kadar tek dosya yükleme</li>
        <li><i class="fa-solid fa-check"></i> Erişim limiti ayarlama</li>
        <li><i class="fa-solid fa-check"></i> Şifreli paylaşım bağlantıları oluşturma</li>
        <li><i class="fa-solid fa-check"></i> Hızlı geri bildirim destek hattı</li>
      </ul>
      <form action="payment.php" method="POST">
  <input type="hidden" name="membership_type" value="monthly">
  <button type="submit" class="satin-btn">Şimdi Yükselt</button>
</form>
    </div>

    <div class="price-card"> 
      <h3 style="font-size: 32px;"> Yıllık Üyelik </h3>    
      <h4 style="font-size: 34px; color: black; width: 100%; height: 30px; ">Fiyat: 499,99 TL</h4>
      <ul>
      <li><i class="fa-solid fa-check"></i> 1TB Bulut depolama alanı </li>  
        <li><i class="fa-solid fa-check"></i> 5 GB'a kadar tek dosya yükleme</li>
        <li><i class="fa-solid fa-check"></i> Gelişmiş dosya arşivleme ve filtreleme</li>
        <li><i class="fa-solid fa-check"></i> Sınırsız dosya yükleme ve paylaşım hakkı</li>
        <li><i class="fa-solid fa-check"></i> Link süresi ve erişim limiti ayarlama</li>
        <li><i class="fa-solid fa-check"></i> Şifreli paylaşım bağlantıları oluşturma</li>
        <li><i class="fa-solid fa-check"></i> Reklamsız şekilde dosya yükleme ve paylaşım</li> 
      </ul>
      <form action="payment.php" method="POST">
  <input type="hidden" name="membership_type" value="yearly">
  <button type="submit" class="satin-btn">Şimdi Yükselt</button>
</form>
    </div>

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
        <li><a href="#">Kullanım Koşulları </a></li>
        <li><a href="#">Gizlilik Politikası </a></li>
        <li><a href="#">Çerez Politikası</a></li> 
        </ul>
        <ul>
        <a href="#"><h3>SOSYAL MEDYA</h3></a>
        <li><a href="">Facebook </a></li>
        <li><a href="">Twitter</a></li>
        <li><a href="">Instagram</a></li> 
        </ul>
        <ul>
        <a href="#"><h3>İLETİŞİM BİLGİLERİ </h3></a>
        <li><a href=""><b>Telefon: </b> +90 123 456 789
        </a></li>
        <li><a href=""><b>Email: </b>tefsharing@gmail.com
        </a></li> 
        </ul>
    </div> 
        <p align="center">Tüm haklar saklıdır. TE-FS &copy2025 <i class="fa-solid fa-signature"></i></p>
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
function openShareModal(fileName, fileId) {
    console.log("Gelen fileId:", fileId); // Bunu ekle
    if (fileName) {
        let shareLink = "http://localhost/finalProject/Frontend/uploads/" + fileName;
        document.getElementById("shareLink").value = shareLink;

        // 💥 Burada file_id'yi de gizli input'a yazıyoruz
        document.getElementById("modalFileId").value = fileId;

        document.getElementById("shareModal").style.display = "block";
    } else {
        console.log("Dosya yolu alınamadı.");
    }
}


function copyLink() {
    var copyText = document.getElementById("shareLink");
    copyText.select();
    copyText.setSelectionRange(0, 99999);  // Mobil cihazlarda da çalışması için

    // Linki kopyala
    document.execCommand("copy");

    // Kopyalandı mesajı göster
    alert("Link panoya kopyalandı: " + copyText.value);
}
function closeModal() {
    document.getElementById("shareModal").style.display = "none";
}


function toggleShareOptions() {
    const shareType = document.getElementById("shareType").value;
    const passwordField = document.getElementById("passwordField");

    if (shareType === "private") {
        passwordField.style.display = "block";
    } else {
        passwordField.style.display = "none";
    }
}
  
    // Paylaşım formu submit olduğunda e-posta gönderme işlemi
    document.getElementById("shareForm").onsubmit = function (event) {
        event.preventDefault(); // Sayfa yeniden yüklenmesin

        var recipient = document.getElementById("recipient").value;
        var shareLink = document.getElementById("shareLink").value;
        var file_id = document.getElementById("modalFileId").value; // Dosya ID'sini alıyoruz

        // AJAX ile verileri PHP'ye gönder
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "shareFile.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        // Gönderilecek veriler
        var data = 
  "recipient=" + encodeURIComponent(recipient) +
  "&file_link=" + encodeURIComponent(shareLink) +
  "&file_id=" + encodeURIComponent(file_id) +
  "&shareType=" + encodeURIComponent(document.getElementById("shareType").value) +
  "&password=" + encodeURIComponent(document.querySelector("[name='password']").value) +
  "&expiry_days=" + encodeURIComponent(document.querySelector("[name='expiry_days']").value) +
  "&max_downloads=" + encodeURIComponent(document.querySelector("[name='max_downloads']").value);

        // İşlem tamamlandığında yapılacaklar
        xhr.onload = function () {
            if (xhr.status === 200) {
                alert(xhr.responseText); // E-posta gönderme durumu
                closeModal(); // Modalı kapat
            } else {
                alert("Bir hata oluştu.");
            }
        };

        xhr.send(data); // Verileri gönder  
    }; 
document.getElementById("showAllBtn").addEventListener("click",  (e) => {
    e.preventDefault(); // formun yeniden yüklenmesini engelle

    // Inputları temizle
    document.querySelector('input[name="type"]').value = '';
    document.querySelector('input[name="min_size"]').value = '';
    document.querySelector('input[name="max_size"]').value = '';

    // Formu sıfırla (gerekliyse)
    document.getElementById('filterForm')?.reset();

    // Sayfayı yeniden yüklemeden tüm dosyaları göstermek için yönlendir
    window.location.href = "upload.php";
});

document.getElementById('dark-mode-toggle').addEventListener('click',()=>{
  document.body.classList.toggle('dark-mode');
});

    </script>
</body>
</html>