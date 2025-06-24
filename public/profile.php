<?php
session_start();
require 'connect.php';  
require 'csrf.php';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT user_name, email, avatar_path, membership_type FROM users WHERE user_id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(); 

$avatar = $user['avatar_path'] ?? null;
 

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
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
<nav class="nav-container">
  <a href="index.php">
    <img src="images/logo.png" alt="Logo" style="width: 80px; margin-right: 111px;" id="logo">
  </a>

  <ul class="nav-links">
    <li><a href="contact.php"><i class="fas fa-envelope icon"></i> İletişim</a></li> 
  </ul>
    <button id="dark-mode-toggle-desktop">
    <i class="fa-solid fa-moon"></i>
  </button>

  <button id="avatarBtn">
    <?php if ($avatar): ?>
      <img src="<?= htmlspecialchars($avatar) ?>" alt="Profil" class="avatar-mini">
    <?php else: ?>
      <i class="fa-solid fa-user-gear"></i>
    <?php endif; ?>
  </button>

  <div class="dropdown" id="dropdownMenu"> 
    <a href="settings.php"><i class="fa-solid fa-cog"></i> Ayarlar</a>
    <a href="archive.php"><i class="fa-solid fa-box"></i> Arşivlerim</a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a>
  </div>

  <div class="hamburger" onclick="openPopup()">☰</div>
</nav>

<div class="popup-overlay" id="popupMenu">
  <div class="popup-menu">
    <span class="close-btn" onclick="closePopup()">&times;</span>
    <ul>
      <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> İletişim</a></li> 
      <li><a href="settings.php"><i class="fa-solid fa-cog"></i> Ayarlar</a></li>
      <li><a href="archive.php"><i class="fa-solid fa-box"></i> Arşivlerim</a></li>
      <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a></li>
      <li>
        <button id="dark-mode-toggle-mobile">
        <i class="fa-solid fa-moon"></i>
        </button> 
      </li>
    </ul>
  </div>
</div>



  </header>
<main>
  <center><?php if ($user['avatar_path']) : ?>
  <img src="<?= htmlspecialchars($user['avatar_path']) ?>" width="120" alt="Avatar" id="avatar">
<?php endif; ?></center>

<form action="profile_update.php" method="POST" enctype="multipart/form-data">  
 <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
<h2 align="center">Merhaba, <?= htmlspecialchars($user['user_name'] ?: $user['user_name']) ?></h2> 
<p><strong>Kullanıcı Adı: </strong>
<input type="text" name="user_name" value="<?= htmlspecialchars($user['user_name']) ?>">
</p>  
  <label><strong>E‑posta:</strong>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
  </label>

  <label><strong>Yeni Avatar Yükle</strong><br>
    <input type="file" name="avatar">
  </label>
<?php if ($avatar): ?>
  <label style="display: block; margin-top: 10px;">
    <input type="checkbox" name="delete_avatar" value="1">
    Mevcut avatarı sil
  </label>
<?php endif; ?>

<button type="submit" class="upgrade">Güncelle</button>

<a href="upload.php" class="back-on">Önceki ekrana dön</a>
<a href="login.php" class="btn_danger">Çıkış Yap</a>
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
            </ul>
        </div> 
            <p align="center">Tüm haklar saklıdır. TE-FS &copy2025</p>
        </footer>
      <script src="assets/profile.js?v=1">
</script>
</body>
</html>
