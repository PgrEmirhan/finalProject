<?php
require 'auth.php';
require 'connect.php';

$id = $_SESSION['user_id'];

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare("SELECT avatar_path, is_profile_public, is_files_public, membership_type FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(); 
$avatar = null;

$avatar = $user['avatar_path'] ?? null;

$stmt->execute([$id]);
$user = $stmt->fetch();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Profilim</title>
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com"> 
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link rel="stylesheet" href="assets/settings.css?v=1">
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
       <button id="dark-mode-toggle">
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
      <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profilim</a></li>
      <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> İletişim</a></li>  
      <li><a href="archive.php"><i class="fa-solid fa-box"></i> Arşivlerim</a></li>
      <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a></li>
            <li>
        <button id="dark-mode-toggle">
        <i class="fa-solid fa-moon"></i>
        </button> 
      </li>
    </ul>
  </div>
</div> 
  </header>
<main class="settings-page">

  <form action="settings_save.php" method="POST" class="setting-card">
    <div class="title-box"><i class="fa-solid fa-lock"></i> <h3> Gizlilik Ayarları</h3></div>
    <label>
      <input type="checkbox" name="is_profile_public" <?= $user['is_profile_public'] ? 'checked' : '' ?>>
      Profilim herkese açık
    </label><br>
    <label>
      <input type="checkbox" name="is_files_public" <?= $user['is_files_public'] ? 'checked' : '' ?>>
      Dosyalar bağlantı ile erişilebilir olsun
    </label><br>
    <button type="submit">Ayarları Kaydet</button>
  </form>

  <form action="change_password.php" method="POST" class="setting-card">
    <div class="title-box"><i class="fa-solid fa-key"></i> Şifre Değiştir</div>
    <label>Eski Şifreniz:
      <input type="password" name="old_password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
         title="Parola en az 8 karakter olmalı, bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir."
         required>
    </label><br>
    <label>Yeni Şifreniz:
      <input type="password" name="new_password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
         title="Parola en az 8 karakter olmalı, bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir."
         required>
    </label><br>
    <label>Yeni Şifre (Tekrar):
      <input type="password" name="confirm_password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
         title="Parola en az 8 karakter olmalı, bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir."
         required>
    </label><br>
    <button type="submit">Şifreyi Güncelle</button>
  </form>

  <form action="update_membership.php" method="POST" class="setting-card">
    <div class="title-box"><i class="fa-solid fa-id-card"></i> Üyelik Türü</div>
    <label>
      <select name="membership_type" required>
        <option value="free" <?= $user['membership_type'] === 'free' ? 'selected' : '' ?>>Ücretsiz</option>
        <option value="monthly" <?= $user['membership_type'] === 'monthly' ? 'selected' : '' ?>>Aylık Üyelik</option>
        <option value="yearly" <?= $user['membership_type'] === 'yearly' ? 'selected' : '' ?>>Yıllık Üyelik</option>
      </select>
    </label>
    <button type="submit" id="membership-select">Üyeliği Güncelle</button>
  </form>

  <div class="setting-card">
    <div class="title-box"><i class="fa-solid fa-database"></i> Veri Yedekleme & Hesap Silme</div>
    <a href="export_data.php" class="btn">Tüm verilerimi indir (JSON)</a>
    <form action="delete_account.php" method="POST" onsubmit="return confirm('Hesabınızı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
      <input type="hidden" name="confirm_delete" value="1">
      <a type="submit" class="danger">Hesabımı kalıcı olarak sil</a>
    </form>
  </div>

  <a href="upload.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Yükleme Sayfasına Dön</a>
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
window.addEventListener('DOMContentLoaded', () => {
  const isDarkMode = localStorage.getItem('darkMode');
  if (isDarkMode === 'enabled') {
    document.body.classList.add('dark-mode');
  }
  updateLogo(); // Sayfa yüklendiğinde logoyu da güncelle
});

function updateLogo() {
  const logo = document.getElementById('logo');
  const isDarkMode = document.body.classList.contains('dark-mode');
  if (logo) {
    logo.src = isDarkMode ? 'images/logo-1.png' : 'images/logo.png';
  }
}

// Butona tıklanınca dark mode aç/kapat ve logoyu güncelle
document.getElementById('dark-mode-toggle').addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
  updateLogo();

  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled');
  } else {
    localStorage.setItem('darkMode', 'disabled');
  }
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

document.getElementById('membership-select').addEventListener('change', function () {
  const selected = this.value;
  const current = "<?= $user['membership_type'] ?>";

  if (selected !== current) {
    document.getElementById('profile-form').action = 'update_membership.php?redirect=1';
    document.getElementById('profile-form').submit();
  }
});  
      // Hamburger popup
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
