
<?php
session_start();
require 'connect.php';
require 'csrf.php';
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    $query = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $query->execute([$_SESSION['user_id']]);
    $user = $query->fetch();
    $avatar = $user['avatar_path'] ?? null;
}
?> 
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>İletişim</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/contact.css?v=1">
</head>
<body>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <header> 
    <nav class="nav-container">
    <a href="index.php" id="logo-container">
      <img src="images/logo.png" alt="Logo" style="width: 80px;" id="logo">
    </a>
  <?php if (!$isLoggedIn): ?>



    <!-- Normal Menü (büyük ekran) -->
    <ul class="nav-links">
    <li><a href="register.php"><i class="fas fa-user-plus icon"></i> Üye Ol</a></li>
    <li><a href="login.php"><i class="fas fa-sign-in"></i> Giriş Yap</a></li> 
    </ul>
    <button id="dark-mode-toggle-desktop-guest">
      <i class="fa-solid fa-moon"></i>
    </button> 
    <!-- Hamburger Menü (mobil) -->
  <div class="hamburger" onclick="openPopup()">☰</div>
  </nav> 
  <!-- Mobil Popup Menü -->
  <div class="popup-overlay" id="popupMenu">
    <div class="popup-menu">
      <span class="close-btn" onclick="closePopup()">&times;</span>
      <ul>
    <li><a href="register.php"><i class="fas fa-user-plus icon"></i> Üye Ol</a></li>
    <li><a href="contact.php"><i class="fas fa-sign-in"></i> Giriş Yap</a></li> 
        <li> <button id="dark-mode-toggle-mobile">   
           <i class="fa-solid fa-moon"></i> 
        </button>
  </li>
  </div>
     <?php endif; ?>
<?php if ($isLoggedIn): ?> 
      
    <!-- Normal Menü (büyük ekran) -->
    <ul class="nav-links"> 
</ul>     
 
    <!-- Avatar Butonu -->
    <button id="avatarBtn">
      <?php if ($avatar): ?>
        <img src="<?= htmlspecialchars($avatar) ?>" alt="Profil" class="avatar-mini">
      <?php else: ?>
        <i class="fa-solid fa-user-gear"></i>
      <?php endif; ?>
    </button>
   <button id="dark-mode-toggle-desktop-user">
      <i class="fa-solid fa-moon"></i>
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
        <li> <button id="dark-mode-toggle-mobile">
      <i class="fa-solid fa-moon"></i>
    </button> 
</li>
      </ul>
    </div>
  </div>  
  <?php endif; ?> 
  </header>
  <main>    
    <?php if (!$isLoggedIn): ?> 

    <div class="container-guest">
      <h3 align="center" style="font-size: 32px;">Destek ve Tavsiye İçin Bizimle İletişime Geçin</h3>
      <form action="contact-mail.php" method="post"> 
   <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="kadi-icon">
      <input type="text" name="kadi" id="kadi" placeholder="adınız..." style="width: 100%;"> <i class="fa-solid fa-user"></i></div>
        <div class="kadi-icon">
      <input type="text" name="email" id="email" placeholder="mail adresiniz..." style="width: 100%;"> <i class="fa-solid fa-envelope"></i></div>
        <div>
        <textarea name="message" id="message" cols="52" rows="6" placeholder="mesajınız..."></textarea>
        </div>
          <input type="submit" value="Gönder" id="gonder-btn">
          <p align="center"> * Tekrar eden mesajlar spama düşecektir *  </p>
        </form>  
    </div>
    <br>  <?php endif; ?> 

<?php if ($isLoggedIn): ?> 
  
    <div class="container-user">
      <h3 align="center" style="font-size: 32px;">Destek ve Tavsiye İçin Bizimle İletişime Geçin</h3>
      <form action="contact-mail.php" method="post"> 
   <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="kadi-icon">
      <input type="text" name="kadi" id="kadi" placeholder="adınız..." style="width: 100%;"> <i class="fa-solid fa-user"></i></div>
        <div class="kadi-icon">
      <input type="text" name="email" id="email" placeholder="mail adresiniz..." style="width: 100%;"> <i class="fa-solid fa-envelope"></i></div>
        <div>
        <textarea name="message" id="message" cols="52" rows="6" placeholder="mesajınız..."></textarea>
        </div>
          <input type="submit" value="Gönder" id="gonder-btn">
          <p align="center"> * Tekrar eden mesajlar spama düşecektir *  </p>
        </form>  
    <a href="upload.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Yükleme Sayfasına Dön</a>
    <?php endif; ?> 


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
document.addEventListener('DOMContentLoaded', () => {
  // Dark mode kontrolü
  const isDarkMode = localStorage.getItem('darkMode');
  if (isDarkMode === 'enabled') {
    document.body.classList.add('dark-mode');
  }

  const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
  updateLogo(); // Sayfa yüklendiğinde logoyu güncelle

  // Logo güncelleme fonksiyonu
  function updateLogo() {
    const logo = document.getElementById('logo');
    const isDark = document.body.classList.contains('dark-mode');
    if (logo) {
      logo.src = isDark ? 'images/logo-1.png' : 'images/logo.png';
    }
  }

  // Dark mode butonları
  const darkModeDesktopBtnG = document.getElementById('dark-mode-toggle-desktop-guest');
  const darkModeDesktopBtnU = document.getElementById('dark-mode-toggle-desktop-user');
  const darkModeMobileBtn = document.getElementById('dark-mode-toggle-mobile');

  function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    updateLogo();
    localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
  }

  if (darkModeDesktopBtnG) {
    darkModeDesktopBtnG.addEventListener('click', toggleDarkMode);
  }

  if (darkModeDesktopBtnU) {
    darkModeDesktopBtnU.addEventListener('click', toggleDarkMode);
  }

  if (darkModeMobileBtn) {
    darkModeMobileBtn.addEventListener('click', toggleDarkMode);
  }

// Logo tıklama logout (sadece oturum açmış kullanıcılar için)
const logoLink = document.getElementById("logo");
if (logoLink && typeof isLoggedIn !== "undefined" && isLoggedIn) {
  logoLink.addEventListener("click", function (e) {
    e.preventDefault();
    const confirmLogout = confirm("Çıkış yapmak istediğinize emin misiniz?");
    if (confirmLogout) {
      window.location.href = "logout.php?redirect=index.php";
    }
  });
}

  // Avatar dropdown
  const avatarBtn = document.getElementById('avatarBtn');
  const dropdown = document.getElementById('dropdownMenu');

  if (avatarBtn && dropdown) {
    avatarBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', function () {
      dropdown.style.display = 'none';
    });
  }

  // Hamburger popup
  function openPopup() {
    document.getElementById("popupMenu").style.display = "block";
  }

  function closePopup() {
    document.getElementById("popupMenu").style.display = "none";
  }

  // Menü dışına tıklanınca dropdown kapanır
  window.addEventListener("click", function (e) {
    const dropdown = document.getElementById("dropdownMenu");
    const avatarBtn = document.getElementById("avatarBtn");

    if (dropdown && avatarBtn && !dropdown.contains(e.target) && !avatarBtn.contains(e.target)) {
      dropdown.style.display = "none";
    }

    const popup = document.getElementById("popupMenu");
    if (popup && e.target === popup) {
      closePopup();
    }
  });
});

</script>
</body>
</html>