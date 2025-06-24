<?php
session_start();
require 'connect.php';

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
</li>
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
<script>// Sayfa yüklendiğinde localStorage'dan dark mode'u kontrol et
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
        const popup = document.getElementById("popupMenu");

    if (!dropdown.contains(e.target) && !avatarBtn.contains(e.target)  && !popup.contains(e.target)) {
      dropdown.style.display = "none";

    } 
  });

  function updateCard(input, elementId) {
    const val = input.value.trim();
    const display = document.getElementById(elementId);
    if (!display) return;

    if (elementId === 'show-card-number') {
      const cleaned = val.replace(/\D/g, '').slice(0, 16);
      const formatted = cleaned.replace(/(.{4})/g, '$1 ').trim();
      input.value = formatted;
      display.innerText = formatted || '**** **** **** ****';
    } else if (elementId === 'show-expiry') {
      let cleaned = val.replace(/\D/g, '').slice(0, 4);
      if (cleaned.length > 2) {
        cleaned = cleaned.slice(0, 2) + '/' + cleaned.slice(2);
      }
      input.value = cleaned;
      display.innerText = cleaned || 'AA/YY';
    } else {
      display.innerText = val || 'Ad Soyad';
    }
  }
  document.addEventListener('DOMContentLoaded', function () {
  const nameInput = document.getElementById('card-name');
  nameInput.addEventListener('input', function () {
    this.value = this.value.replace(/[^a-zA-ZğüşöçıİĞÜŞÖÇ\s]/g, '');
  });

  // Başlangıçta kart açısını ayarla
  applyInitialCardRotations();
});

function applyInitialCardRotations() {
  const cards = document.querySelectorAll('.card-container');
  const selectedCard = document.querySelector('.card-container.selected');
  let angleOptions = ['rotate(160deg)', 'rotate(380deg)'];
  let angleIndex = 0;

  cards.forEach(card => {
    if (card === selectedCard) {
      card.style.transform = 'translateY(-20px) scale(1.05)';
    } else {
      card.style.transform = angleOptions[angleIndex];
      angleIndex++;
    }
  });
}

function selectCard(selectedCard, selectedType) {
  const cards = document.querySelectorAll('.card-container');
  let angleOptions = ['rotate(160deg)', 'rotate(380deg)'];
  let angleIndex = 0;

  cards.forEach(card => {
    card.classList.remove('selected');

    if (card === selectedCard) {
      card.style.transform = 'translateY(-20px) scale(1.05)';
    } else {
      card.style.transform = angleOptions[angleIndex];
      angleIndex++;
    }
  });

  selectedCard.classList.add('selected');
  document.getElementById('card-type').value = selectedType;
}

</script>

</body>
</html>
