<?php
require 'connect.php';
require 'auth.php';

$id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT user_name, email, avatar_path, membership_type FROM users WHERE user_id=?");
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
  <link rel="stylesheet" href="assets/profile.css?v=1">
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
      <button id="dark-mode-toggle">
    <i class="fa-solid fa-moon"></i>
  </button>

  </ul>
 

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
      <li><a href="settings.php"><i class="fa-solid fa-cog"></i> Ayarlar</a></li>
      <li><a href="archive.php"><i class="fa-solid fa-box"></i> Arşivlerim</a></li>
      <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a></li>
    </ul>
  </div>
</div>



  </header>
<main>
  <center><?php if ($user['avatar_path']) : ?>
  <img src="<?= htmlspecialchars($user['avatar_path']) ?>" width="120" alt="Avatar" id="avatar">
<?php endif; ?></center>

<form action="profile_update.php" method="POST" enctype="multipart/form-data"> 

<h2>Merhaba, <?= htmlspecialchars($user['user_name'] ?: $user['user_name']) ?></h2> 
<p><strong>Kullanıcı Adı: </strong>
<input type="text" name="user_name" value="<?= htmlspecialchars($user['user_name']) ?>">
</p>  
  <label>E‑posta
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
  </label>

  <label>Yeni Avatar Yükle
    <input type="file" name="avatar">
  </label>

<a type="submit" class="upload">Güncelle</a>

<a href="change_password.php" class="btn">Şifre Değiştir</a>
<a href="upload.php" class="back-on">Önceki ekrana dön</a>
<a href="login.php" class="btn danger">Çıkış Yap</a>
</form>

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
            <p align="center">Tüm haklar saklıdır. TE-FS &copy2025</p>

  </ul> 

        </div> 
        </footer>
      <script>
 // Sayfa yüklendiğinde localStorage'dan dark mode'u kontrol et
window.addEventListener('DOMContentLoaded', () => {
  const isDarkMode = localStorage.getItem('darkMode');
  if (isDarkMode === 'enabled') {
    document.body.classList.add('dark-mode');
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


// Butona tıklanınca dark mode aç/kapat
document.getElementById('dark-mode-toggle').addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');

  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled'); // aktif halde sakla
  } else {
    localStorage.setItem('darkMode', 'disabled'); // kapalı olarak sakla
  }
});   // Hamburger popup
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
