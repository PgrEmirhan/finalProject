<?php
session_start();

require 'connect.php';
require 'csrf.php';
require 'auth.php'; 

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Kullanıcı oturumu bulunamadı.");
}

$membership_type = $_POST['membership_type'] ?? null;
$valid_types = ['monthly', 'yearly'];
if (!in_array($membership_type, $valid_types)) {
    die("Geçersiz üyelik türü.");
}

try {
    $stmt = $pdo->prepare("UPDATE users SET membership_type = ? WHERE user_id = ?");
    $stmt->execute([$membership_type, $user_id]);

    unset($_SESSION['selected_membership']);

    echo "<p>Ödeme başarılı. Üyelik türünüz <strong>" . htmlspecialchars($membership_type) . "</strong> olarak güncellendi.</p>";
    echo "<a href='upload.php'>Ana Sayfaya Dön</a>";

} catch (PDOException $e) {
    echo "Hata oluştu: " . htmlspecialchars($e->getMessage());
}
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    $query = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $query->execute([$_SESSION['user_id']]);
    $user = $query->fetch();
    $avatar = $user['avatar_path'] ?? null;
}
?>



<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ödeme Sayfası</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&family=Ubuntu&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
        <link rel="stylesheet" href="assets/confirm.css">
</head>
<body>

<header>
  <nav class="nav-container">
    <a href="index.php" id="logo-container">
      <img src="images/logo.png" alt="Logo" style="width: 80px;" id="logo">
    </a>
    <ul class="nav-links">
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
      <a href="profile.php"><i class="fa-solid fa-user"></i> Profilim</a>
      <a href="settings.php"><i class="fa-solid fa-cog"></i> Ayarlar</a>
      <a href="archive.php"><i class="fa-solid fa-box"></i> Arşivlerim</a>
      <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a>  
</li>
    </div>

    <div class="hamburger" onclick="openPopup()">☰</div>
  </nav>

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

</header>
 
<main>
  <div style="text-align: center; margin-top: 80px;">
    <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Başarılı" width="120">
    <h2 style="color: green;">Üyeliğiniz Başarıyla Güncellendi!</h2>
    <p>Ödemeniz başarıyla alındı. Artık özel üyelik ayrıcalıklarından yararlanabilirsiniz.</p>
    <p style="font-weight: bold; margin-top: 10px;">Gelişmiş dosya yükleme, hızlı indirme ve reklamsız deneyim sizi bekliyor!</p>
    <a href="upload.php" style="display: inline-block; margin-top: 25px; padding: 12px 30px; background-color: black; color: white; border-radius: 5px; text-decoration: none;">Yükleme ekranına dön</a>
      <a href="settings.php" style="display: inline-block; margin-top: 25px; padding: 12px 30px; background-color: black; color: white; border-radius: 5px; text-decoration: none;">Ayarlar ekranına dön</a>

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
<script src="assets/confirm.js?v=1">
</script>

</body>
</html>