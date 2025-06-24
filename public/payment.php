<?php 
session_start();
require 'connect.php';
require 'csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini al (avatar için vs.)
$query = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$query->execute([$user_id]);
$user = $query->fetch();
$avatar = $user['avatar_path'] ?? null;

// Eğer formdan üyelik tipi gelmişse, session’a ata:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['membership_type'])) {
    $_SESSION['selected_membership'] = $_POST['membership_type'];
} elseif (!isset($_SESSION['selected_membership'])) {
    // Ne POST ne SESSION'da bilgi varsa upload'a geri gönder
    header("Location: upload.php");
    exit;
}
?>

  <link rel="stylesheet" href="assets/payment.css">
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
  <title>Ödeme Sayfası</title>  
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://fonts.googleapis.com/css2?family=Inter&family=Ubuntu&family=Roboto:wght@400;700&display=swap" rel="stylesheet"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">> <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
</head>
<body>

<header>    
  <nav class="nav-container">
    <a href="index.php" id="logo-container">
      <img src="images/logo.png" alt="Logo" style="width: 80px;" id="logo">
    </a>
    <!-- Normal Menü (büyük ekran) -->
    <ul class="nav-links">
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

</header>

<main>
  <div id="payment-wrapper">
    <h2><i class="fa-solid fa-credit-card"></i> ÖDEME SAYFASI <i class="fa-solid fa-credit-card"></i></h2>
    
    <div id="payment-content">
      <div class="payment-image">
        <img src="images/payment.png" alt="">
      </div>

      <div class="stick"></div>

      <div class="wrapper">
        <div class="card-selection">
          <div class="card-stack">
            <div class="card-container visa selected" onclick="selectCard(this, 'visa')">
              <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" class="card-logo" alt="Visa">
              <div class="card-title">Visa</div>
              <div class="chip"></div>
              <div class="card-info" id="show-card-number">**** **** **** ****</div>
              <div class="card-footer">
                <div id="show-name">Ad Soyad</div>
                <div id="show-expiry">AA/YY</div>
              </div>
            </div>

            <div class="card-container mastercard" onclick="selectCard(this, 'mastercard')">
              <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Mastercard-logo.png" class="card-logo" alt="Mastercard">
              <div class="card-title">Mastercard</div>
              <div class="chip"></div>
              <div class="card-info" id="show-card-number-mastercard">**** **** **** ****</div>
              <div class="card-footer">
                <div id="show-name-mastercard">Ad Soyad</div>
                <div id="show-expiry-mastercard">AA/YY</div>
              </div>
            </div>

            <div class="card-container troy" onclick="selectCard(this, 'troy')">
              <img src="images/Troy.png" class="card-logo" alt="TROY">
              <div class="card-title">TROY</div>
              <div class="chip"></div>
              <div class="card-info" id="show-card-number-troy">**** **** **** ****</div>
              <div class="card-footer">
                <div id="show-name-troy">Ad Soyad</div>
                <div id="show-expiry-troy">AA/YY</div>
              </div>
            </div>
          </div>
        </div>
        <br><br><br>

        <form class="form-container" action="confirm.php" method="POST">   
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="card_type" id="card-type" value="visa">
            <input type="hidden" name="membership_type" value="<?php echo htmlspecialchars($_POST['membership_type'] ?? ''); ?>">

          <label>Kart Numarası</label>
          <input type="text" name="card_number" maxlength="19" oninput="updateCard(this, 'card-number')" placeholder="1234 5678 9012 3456" required>

          <label>Ad Soyad</label>
          <input type="text" name="card_name" oninput="validateName(this); updateCard(this, 'name')" placeholder="Ad Soyad" required>

          <label>Son Kullanma Tarihi</label>
          <input type="text" name="expiry_date" maxlength="5" oninput="updateCard(this, 'expiry')" placeholder="MM/YY" required>

          <label>CVV</label>
          <input type="password" name="cvv" maxlength="3" oninput="validateCVV(this)" placeholder="***" required>

          <input type="submit" value="Ödeme Yap">
        </form>
      </div>
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
<script src="assets/payment.js?v=1">
</script>
</body>
</html>
