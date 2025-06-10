<?php
    session_start();
    require 'connect.php';

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
    
    $user_id = $_SESSION['user_id'];   
    $username = $_SESSION['user_name'];

$avatar = null;

$query = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();
if (!$user) {
    // Kullanıcı bulunamadıysa hata göster
    echo "<p class='error-msg'>Kullanıcı bulunamadı.</p>";
    exit;
}

$avatar = $user['avatar_path'] ?? null;
$membership = $user['membership_type']; // Üyelik türünü alıyoruz
$sql = "SELECT * FROM files WHERE user_id = ? and is_archived=0";
$params = [$user_id];

if ($membership != 'free') {
    if (!empty($_POST['type'])) {
        $sql .= " AND file_name LIKE ?";
        $params[] = "%." . ltrim($_POST['type'], '.'); // baştaki noktayı temizle
    }

    if (!empty($_POST['min_size']) && is_numeric($_POST['min_size'])) {
        $sql .= " AND file_size >= ?";
        $params[] = (int)$_POST['min_size'];
    }

    if (!empty($_POST['max_size']) && is_numeric($_POST['max_size'])) {
        $sql .= " AND file_size <= ?";
        $params[] = (int)$_POST['max_size'];
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
     // KOTA KONTROLÜ
    if (($totalUsed + $fileSize) > $maxQuota) {
        $uploadMessage = "<p class='error-msg'>Yükleme sınırınızı aştınız. Üyeliğinize uygun maksimum kapasiteyi doldurdunuz.</p>";
    } elseif ($fileSize > $maxFileSize) {
        $uploadMessage = "<p class='error-msg'>Bu dosya üyelik türünüz için çok büyük. Maksimum izin verilen dosya boyutu: " . round($maxFileSize / 1024 / 1024) . " MB</p>";
    } else {
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

    }

// Dosya silme işlemi
if (isset($_POST['delete_file'])) {
    $fileId = $_POST['delete_file'];
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
 
        if (isset($_POST['share_file'])) {
            $file_id_to_share = $_POST['share_file'];

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Yükle</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">  
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="assets/upload.css?v=1">
  </head>
<body>
  <header>
  <!-- NAV -->
  <nav class="nav-container">
    <a href="index.php">
      <img src="images/logo.png" alt="Logo" style="width: 80px; margin-right: 111px;">
    </a>

    <!-- Normal Menü (büyük ekran) -->
    <ul class="nav-links">
      <li><a href="contact.php"><i class="fas fa-envelope icon"></i> İletişim</a></li>

    </ul>

        <button id="dark-mode-toggle">
      <i class="fa-solid fa-moon"></i>
    </button>
    <!-- Dark Mode -->

    <!-- Gizli Profil İkonu -->
<?php if (isset($user['is_profile_public']) && !$user['is_profile_public']): ?>
      <span title="Profiliniz gizli 🔐">🔐</span>
    <?php endif; ?>

    <!-- Avatar Butonu -->
    <button id="avatarBtn">
      <?php if ($avatar): ?>
        <img src="<?= htmlspecialchars($avatar) ?>" alt="Profil" class="avatar-mini">
      <?php else: ?>
        <i class="fa-solid fa-user-gear"></i>
      <?php endif; ?>
    </button>

    <!-- Dropdown Menü (masaüstü) -->
    <div class="dropdown" id="dropdownMenu">
      <a href="profile.php"><i class="fa-solid fa-user"></i> Profilim</a>
      <a href="settings.php"><i class="fa-solid fa-cog"></i> Ayarlar</a>
      <a href="archive.php"><i class="fa-solid fa-box"></i> Arşivlerim</a>
      <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a>
    </div>

    <!-- Hamburger Menü (mobil) -->
    <div class="hamburger" onclick="openPopup()">☰</div>
  </nav>

  <!-- Mobil Popup Menü -->
  <div class="popup-overlay" id="popupMenu">
    <div class="popup-menu">
      <span class="close-btn" onclick="closePopup()">&times;</span>
      <ul>
        <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> İletişim</a></li>
        <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profilim</a></li>
        <li><a href="settings.php"><i class="fa-solid fa-cog"></i> Ayarlar</a></li>
        <li><a href="archive.php"><i class="fa-solid fa-box"></i> Arşivlerim</a></li>
        <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a></li>
      </ul>
    </div>
  </div>

  </header>
  <main>
    <div class="upload">
        <h2 style="font-size: 25px; ">Hoş geldiniz, <?php echo htmlspecialchars($username); ?> !</h2>   
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
        <form id="filterForm" method="POST" action="upload.php">
        <h3>Filtreleme</h3>
        <label>Tür (uzantı, örn: txt, pdf):</label>
        <input type="text" name="type" id="type" value="<?= htmlspecialchars($_POST['type'] ?? '') ?>">
        <br>
        <label>Minimum Boyut (bayt):</label>
        <input type="number" id="min-size" name="min_size" value="<?= htmlspecialchars($_POST['min_size'] ?? '') ?>">
        <br>
        <label>Maksimum Boyut (bayt):</label>
        <input type="number" name="max_size" id="max-size" value="<?= htmlspecialchars($_POST['max_size'] ?? '') ?>">
        <br>
        <input type="submit" value="Filtrele" id="filter-btn">
        <button id="showAllBtn">Hepsini Getir</button>
    </form><br><br>
    <?php endif; ?>


<br><br>
        <div class="file-list">
            <h3>Yüklediğiniz Dosyalar:</h3>
          <form id="archiveForm" method="POST" action="archive.php">
    <?php if (count($files) > 0): ?>
        <?php foreach ($files as $file): ?>
            <div>
                <?php if($membership!=='free' ):?>
                <input type="checkbox" name="files[]" value="<?= htmlspecialchars($file['file_path'])  ?>">
                <?php endif;?>
                <label style="cursor: pointer;">                
                <?= htmlspecialchars($file['file_name']) ?> (<?= $file['file_size'] ?> bayt)          
                </label>

        <!-- İndir -->
        <a href="uploads/<?= basename($file['file_path']) ?>" download>
            <button type="button">İndir</button>
        </a>
        <button type="button" onclick="if(confirmDelete(<?= $file['file_id']; ?>)){ window.location='upload.php?delete_file=<?= $file['file_id']; ?>'; }">
          Sil
        </button>
            <button type="button" onclick="openShareModal('<?= addslashes(htmlspecialchars(basename($file['file_path']))) ?>');">
                Paylaş
            </button>
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
    <label>Parola (isteğe bağlı): </label><br>
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
        <h4 style="font-size: 34px; width: 100%; height: 30px; ">Fiyat: 199,99 TL</h4> 
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
      <h4 style="font-size: 34px; width: 100%; height: 30px; ">Fiyat: 499,99 TL</h4>
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
 document.addEventListener('DOMContentLoaded', () => {
  // Dark mode kontrolü
  const isDarkMode = localStorage.getItem('darkMode');
  if (isDarkMode === 'enabled') {
    document.body.classList.add('dark-mode');
  }

  document.getElementById('dark-mode-toggle').addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
  });

  // Avatar dropdown
  const avatarBtn = document.getElementById('avatarBtn');
  const dropdown = document.getElementById('dropdownMenu');

  avatarBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  });

  document.addEventListener('click', function () {
    dropdown.style.display = 'none';
  });

  // Dosya yükleme alanları
  const dropArea = document.getElementById('drop-area');
  const fileInput = document.getElementById('fileInput');
  const progressBar = document.getElementById('progress-bar');
  let filesToUpload = [];

  dropArea.addEventListener('dragover', function (e) {
    e.preventDefault();
    dropArea.classList.add('hover');
  });

  dropArea.addEventListener('dragleave', function (e) {
    e.preventDefault();
    dropArea.classList.remove('hover');
  });

  dropArea.addEventListener('drop', function (e) {
    e.preventDefault();
    dropArea.classList.remove('hover');
    const files = e.dataTransfer.files;
    handleFileSelection(files);
  });

  fileInput.addEventListener('change', function (e) {
    const files = e.target.files;
    handleFileSelection(files);
  });

  function handleFileSelection(files) {
    filesToUpload = files;
    document.querySelector('.upload-btn').disabled = false;
  }

  document.querySelector('.upload-btn')?.addEventListener('click', uploadFile);

  function uploadFile() {
    if (filesToUpload.length === 0) {
      alert("Lütfen bir dosya seçin.");
      return;
    }

    const formData = new FormData();
    formData.append("file", filesToUpload[0]);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "upload.php", true);

    xhr.upload.addEventListener("progress", updateProgressBar, false);

    xhr.onload = function () {
      if (xhr.status === 200) {
        progressBar.style.width = '100%';
        progressBar.innerHTML = 'Yükleme Tamamlandı!';
        setTimeout(() => {
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
    const percentage = (event.loaded / event.total) * 100;
    progressBar.style.width = percentage + '%';
    progressBar.innerHTML = Math.round(percentage) + '%';
  }

  // Paylaş modalı ve paylaşım işlemleri
  document.getElementById("shareForm")?.addEventListener("submit", function (event) {
    event.preventDefault();

    const recipient = document.getElementById("recipient").value;
    const shareLink = document.getElementById("shareLink").value;
    const file_id = document.getElementById("modalFileId").value;

    const data =
      "recipient=" + encodeURIComponent(recipient) +
      "&file_link=" + encodeURIComponent(shareLink) +
      "&file_id=" + encodeURIComponent(file_id) +
      "&shareType=" + encodeURIComponent(document.getElementById("shareType").value) +
      "&password=" + encodeURIComponent(document.querySelector("[name='password']").value) +
      "&expiry_days=" + encodeURIComponent(document.querySelector("[name='expiry_days']").value) +
      "&max_downloads=" + encodeURIComponent(document.querySelector("[name='max_downloads']").value);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "shareFile.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
      if (xhr.status === 200) {
        alert(xhr.responseText);
        closeModal();
      } else {
        alert("Bir hata oluştu.");
      }
    };

    xhr.send(data);
  });

  document.getElementById("showAllBtn")?.addEventListener("click", (e) => {
    e.preventDefault();
    document.querySelector('input[name="type"]').value = '';
    document.querySelector('input[name="min_size"]').value = '';
    document.querySelector('input[name="max_size"]').value = '';
    document.getElementById('filterForm')?.reset();
    window.location.href = "upload.php";
  });

});

// Diğer fonksiyonlar (DOMContentLoaded dışında olabilir):
function triggerFileInput() {
  document.getElementById('fileInput').click();
}

function openShareModal(fileName, fileId) {
  if (fileName) {
    const shareLink = "http://localhost/finalProject/Frontend/uploads/" + fileName;
    document.getElementById("shareLink").value = shareLink;
    document.getElementById("modalFileId").value = fileId;
    document.getElementById("shareModal").style.display = "block";
        document.getElementById("overlay").style.display = "block";

  } else {
    console.log("Dosya yolu alınamadı.");
  }
}
    
function confirmDelete(fileId) {
    if (confirm('Emin misiniz? Bu dosya kalıcı olarak silinecek?')) {
          const formData = new FormData();
        formData.append('delete_file', fileId);
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                alert('Dosya başarıyla silindi.');
                location.reload();
            } else {
                alert('Dosya silinirken bir hata oluştu.');
            }
        };
        xhr.send(formData);
    }
}
function copyLink() {
  const copyText = document.getElementById("shareLink");
  copyText.select();
  copyText.setSelectionRange(0, 99999);
  document.execCommand("copy");
  alert("Link panoya kopyalandı: " + copyText.value);
}

function closeModal() {
  document.getElementById("shareModal").style.display = "none";
      document.getElementById("overlay").style.display = "none";

}

function toggleShareOptions() {
  const shareType = document.getElementById("shareType").value;
  const passwordField = document.getElementById("passwordField");
  passwordField.style.display = (shareType === "private") ? "block" : "none";
}
  // Hamburger popup
  function openPopup() {
    document.getElementById("popupMenu").style.display = "flex";
  }

  function closePopup() {
    document.getElementById("popupMenu").style.display = "none";
  }

  // Menü dışına tıklanınca dropdown kapanır
  window.addEventListener("click", function (e) {
    const dropdown = document.getElementById("dropdownMenu");
    const avatarBtn = document.getElementById("avatarBtn");
    if (!dropdown.contains(e.target) && !avatarBtn.contains(e.target)) {
      dropdown.style.display = "none";
    }

    const popup = document.getElementById("popupMenu");
    if (e.target === popup) {
      closePopup();
    }
  });

    </script>
</body>
</html>