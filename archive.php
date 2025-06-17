  <?php
  session_start();
  require 'connect.php';
  require 'csrf.php';

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  require 'src/PHPMailer.php';
  require 'src/SMTP.php';
  require 'src/Exception.php';

  $user_id = $_SESSION['user_id'] ?? null;
  if (!$user_id) {
      die("Yetkisiz erişim");
  }

  // Arşivleme işlemi
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['files'])) {
      $filePaths = $_POST['files'];
      $stmt = $pdo->prepare("UPDATE files SET is_archived = 1 WHERE user_id = ? AND file_path = ?");
      foreach ($filePaths as $path) {
          $stmt->execute([$user_id, $path]);
      }
      header("Location: archive.php?archived=1");
      exit;
  } 

  $avatar = null;
  $stmt = $pdo->prepare("SELECT avatar_path, is_profile_public, is_files_public, membership_type FROM users WHERE user_id = ?");
  $stmt->execute([$user_id ]);
  $user = $stmt->fetch(); 

  $avatar = $user['avatar_path'] ?? null;

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $recipientEmail = $_POST['recipient'];
      $fileLink = $_POST['file_link'];

      $mail = new PHPMailer(true);

      try {
          // Sunucu ayarları
          $mail->isSMTP();
          $mail->Host       = 'smtp.gmail.com'; 
          $mail->SMTPAuth   = true;
          $mail->Username   = 'tefsharing@gmail.com';
          $mail->Password   = 'vmze zuwg xorr vasq'; // Gmail için uygulama şifresi gerekir!
          $mail->SMTPSecure = 'tls';
          $mail->Port       = 587;

          // Alıcı ve içerik
          $mail->setFrom('emirhankot423@gmail.com', 'Dosya Paylaşım');
          $mail->addAddress($recipientEmail);
          $mail->Subject = 'Paylaşılan Dosya Linki';
          $mail->Body    = "Merhaba,\n\nBu mesaj, bir dosya paylaşımı içermektedir. Aşağıdaki linkten dosyayı indirebilirsiniz:\n\n" . $fileLink;

          $mail->send();
          echo 'Dosya başarıyla paylaşıldı!';
      } catch (Exception $e) {
          echo "E-posta gönderilemedi. Hata: {$mail->ErrorInfo}";
      }
  }

  // Arşivlenmiş dosyalar
  $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? AND is_archived = 1 ORDER BY uploaded_at DESC");
  $stmt->execute([$user_id]);
  $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <!DOCTYPE html>
  <html lang="tr">
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

      <link rel="stylesheet" href="assets/archive.css?v=1">
    </head>
  <body>
    <header>
  <!-- NAV -->
  <nav class="nav-container">
    <a href="index.php">
      <img src="images/logo.png" alt="Logo" style="width: 80px; margin-right: 111px;" id="logo">
    </a>

    <!-- Normal Menü (büyük ekran) -->
    <ul class="nav-links">
      <li><a href="contact.php"><i class="fas fa-envelope icon"></i> İletişim</a></li>

    </ul>
        <button id="dark-mode-toggle-desktop">
      <i class="fa-solid fa-moon"></i>
    </button>


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
        <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profilim</a></li>
        <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> İletişim</a></li>  
        <li><a href="settings.php"><i class="fa-solid fa-cog"></i> Ayarlar</a></li>
        <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a></li>
          <li>
          <button id="dark-mode-toggle-mobile">
          <i class="fa-solid fa-moon"></i>
          </button> 
        </li>
      </ul>
    </div>
  </div> 
      </nav>
    </header>
    <main>
      <div class="file-list">
      <h2 align="center">📦 Arşivlenen Dosyalar</h2>

      <?php if (empty($files)): ?>
          <p>Hiç arşivlenmiş dosyanız yok.</p>
      <?php else: ?>
          <ul>
              <?php foreach ($files as $file): ?>
              <li>
                  <strong><?= htmlspecialchars($file['file_name']) ?></strong>
                  (<?= round($file['file_size'] / 1024, 2) ?> KB) <br>  
                  Yüklenme Tarihi: <?= htmlspecialchars($file['uploaded_at']) ?>  - 
                  <form method="POST" action="unarchives.php" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                      <input type="hidden" name="file_id" value="<?= $file['file_id'] ?>">
                      <button type="submit">Arşivden Çıkar</button> 
                  </form>
              </li>
              <?php endforeach; ?>
          </ul>
      <?php endif; ?>   
      
      <a href="upload.php" id="back-upload">Yükleme sayfasına dön</a>

      </div>

  <!-- Modal -->
  <div id="overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:#00000088; z-index:999;"></div>
  <div id="shareModal" style="display:none; position:fixed; top:20%; left:35%; width:30%; background:white; padding:20px; border:1px solid #ccc; z-index:1000;">
      <h3>Paylaşım Ayarları</h3>
      <form id="shareForm" action="archive.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

          <label for="shareType">Paylaşım Türü:</label>
          <select id="shareType" name="shareType" required onchange="toggleShareOptions()">
              <option value="public">Genel</option>
              <option value="private">Özel</option>
          </select><br><br>

          <div id="passwordField" style="display: none;">
              <label>Parola (isteğe bağlı):</label>
              <input type="text" name="password"><br><br>
          </div>

          <label>Geçerlilik süresi (gün):</label>
          <input type="number" name="expiry_days" min="1" value="7"><br><br>

          <label>Max indirme sayısı (isteğe bağlı):</label>
          <input type="number" name="max_downloads" min="1"><br><br>

          <label>Paylaşım Linki:</label>
          <input type="text" id="shareLink" name="file_link" readonly />
          <button type="button" onclick="copyLink()">Kopyala</button><br><br>

          <label>Paylaşılacak E-Posta:</label>
          <input type="email" id="recipient" name="recipient" required><br><br>

          <input type="hidden" name="file_id" id="modalFileId">

          <button type="submit">Paylaş</button>
          <button type="button" onclick="closeModal()">İptal</button>
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
              <li><a href="legal/terms-of-use.html">Kullanım Koşulları </a></li>
              <li><a href="legal/privacy-policy.html">Gizlilik Politikası </a></li>
              <li><a href="legal/cookie-policy.html">Çerez Politikası</a></li> 
              </ul>
              <ul>
              <a href="#"><h3>SOSYAL MEDYA</h3></a>
              <li><a href="#">Facebook </a></li>
              <li><a href="#">X</a></li>
              <li><a href="#">Instagram</a></li> 
              </ul>
              <ul>
              <a href="#"><h3>İLETİŞİM BİLGİLERİ </h3></a>
              <li><a href="#"><b>Telefon: </b> +90 123 456 789
              </a></li>
              <li><a href="mailto: tefsharing@gmail.com"><b>Email: </b>tefsharingt@gmail.com
              </a></li>  
              </ul>
          </div> 
              
              <p align="center">Tüm haklar saklıdır. TE-FS &copy2025</p>
          </footer>

  <script>
  // Sayfa yüklendiğinde localStorage'dan dark mode'u kontrol et
  window.addEventListener('DOMContentLoaded', () => {
    const isDarkMode = localStorage.getItem('darkMode');
    if (isDarkMode === 'enabled') {
      document.body.classList.add('dark-mode');
    }
    updateLogo(); // Sayfa yüklendiğinde logoyu da güncelle

  function updateLogo() {
    const logo = document.getElementById('logo');
    const isDarkMode = document.body.classList.contains('dark-mode');
    if (logo) {
      logo.src = isDarkMode ? 'images/logo-1.png' : 'images/logo.png';
    }
  }

  // Butona tıklanınca dark mode aç/kapat ve logoyu güncelle
  document.getElementById('dark-mode-toggle-desktop').addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    updateLogo(); // Sayfa yüklendiğinde logoyu da güncelle

    if (document.body.classList.contains('dark-mode')) {
      localStorage.setItem('darkMode', 'enabled');
    } else {
      localStorage.setItem('darkMode', 'disabled');
    }
  });
  // Butona tıklanınca dark mode aç/kapat ve logoyu güncelle
  document.getElementById('dark-mode-toggle-mobile').addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    updateLogo(); // Sayfa yüklendiğinde logoyu da güncelle

    if (document.body.classList.contains('dark-mode')) {
      localStorage.setItem('darkMode', 'enabled');
    } else {
      localStorage.setItem('darkMode', 'disabled');
    }
  });
    const avatarBtn = document.getElementById('avatarBtn');
    const dropdown = document.getElementById('dropdownMenu');

    avatarBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', function () {
      dropdown.style.display = 'none';
    });

  function openShareModal(fileName, fileId) {
      const link = "http://localhost/finalProject/frontend/uploads/" + fileName;
      document.getElementById("shareLink").value = link;
      document.getElementById("modalFileId").value = fileId;
      document.getElementById("shareModal").style.display = "block";
      document.getElementById("overlay").style.display = "block";
  }

  function closeModal() {
      document.getElementById("shareModal").style.display = "none";
      document.getElementById("overlay").style.display = "none";
  }

  function copyLink() {
      const copyText = document.getElementById("shareLink");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      document.execCommand("copy");
      alert("Link panoya kopyalandı: " + copyText.value);
  }

  function toggleShareOptions() {
      const shareType = document.getElementById("shareType").value;
      document.getElementById("passwordField").style.display = (shareType === "private") ? "block" : "none";
  }
  // Butona tıklanınca dark mode aç/kapat
  document.getElementById('dark-mode-toggle').addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
  }); 
  });

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
