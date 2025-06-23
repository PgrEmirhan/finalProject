<?php
session_start();

require 'connect.php';
require 'csrf.php';


// Form gönderildiyse işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];
    $input_email = $_POST['email'];
 
    if (!filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
        echo "Geçersiz e-posta adresi!";
        exit();
    }
 
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_name = ? OR email = ?");
    $stmt->execute([$input_username, $input_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "Bu kullanıcı adı ya da e posta zaten alınmış!";
    } else { 
        $hashed_password = password_hash($input_password, PASSWORD_DEFAULT);
 
        $is_guest = 0;
 
        $stmt = $pdo->prepare("INSERT INTO users (user_name, user_password, email, is_guest) VALUES (?, ?, ?, ?)");
        $stmt->execute([$input_username, $hashed_password, $input_email, $is_guest]);
        header("Location: login.php");
        exit();
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kayıt Ol</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/register.css">
</head>
<body>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <header>
<!-- NAVIGATION BAR -->
<nav class="nav-container">
  <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px;" id="logo"></a>

  <!-- NORMAL MENÜ (büyük ekranlar için) -->
  <ul class="nav-links">
    <li><a href="login.php"><i class="fas fa-sign-in"></i> Giriş Yap</a></li>
    <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> İletişim</a></li>
  </ul>
<button id="dark-mode-toggle-desktop">
 <i class="fa-solid fa-moon"></i>
</button>

  <!-- HAMBURGER ICON (küçük ekranlar için) -->
  <div class="hamburger" onclick="openPopup()">☰</div>
</nav>

<!-- POPUP MENÜ -->
<div class="popup-overlay" id="popupMenu">
  <div class="popup-menu"> 
    <ul>
    <li><a href="login.php"><i class="fas fa-sign-in"></i> Giriş Yap</a></li>
    <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> İletişim</a></li>

   <!-- DARK MODE BUTTON -->
      <li><button id="dark-mode-toggle-mobile">
         <i class="fa-solid fa-moon"></i>
        </button>
</li>
    </ul>
  </div>
</div>
  </header>
  <main>    
    <div class="container">
      <h3 align="center" style="font-size: 32px;">Kayıt Ol</h3>
      <form action="" method="post"> 
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="uname-icon">
      <input type="text" name="username" id="uname" placeholder="kullanıcı adınız..." style="width: 100%;" pattern="^[a-zA-Z0-9._]{1,30}$"
                title="Kullanıcı adı yalnızca harf, rakam, nokta (.) ve alt çizgi (_) içerebilir. Boşluk karakteri kullanılamaz." 
                required> 
      <i class="fa-solid fa-user"></i></div>
       <div class="email-icon"> 
        <input type="email" name="email" id="email" placeholder="mail adresiniz..." style="width: 100%;" required>
        <i class="fa-solid fa-envelope"></i>
       </div>     
        <div class="pword-icon">
          <input type="password" name="password" id="pword"placeholder="parolanız..." style="width: 100%;" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
         title="Parola en az 8 karakter olmalı, bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir."
         required> 
          <i class="fa-solid fa-eye-slash" id="togglePassword"></i>
          </div>
          <input type="submit" value="Kayıt Ol" id="kayit-btn">
          <p align="center">Zaten hesabınız var mı? <a href="login.php">Giriş yapın!</a> 
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
    const togglePassword = document.getElementById("togglePassword");
    const passwordField = document.getElementById("pword");

    togglePassword.addEventListener("click", function () {
        const type = passwordField.type === "password" ? "text" : "password";
        passwordField.type = type;
        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");
    });
 // Sayfa yüklendiğinde localStorage'dan dark mode'u kontrol et
window.addEventListener('DOMContentLoaded', () => {
  const isDarkMode = localStorage.getItem('darkMode');
  if (isDarkMode === 'enabled') {
    document.body.classList.add('dark-mode');
  }
  updateLogo();
});
function updateLogo() {
  const logo = document.getElementById('logo');
  const isDarkMode = document.body.classList.contains('dark-mode');
  if (logo) {
    logo.src = isDarkMode ? 'images/logo-1.png' : 'images/logo.png';
  }
}

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
  function openPopup() {
    document.getElementById("popupMenu").style.display = "flex";
  }

  function closePopup() {
    document.getElementById("popupMenu").style.display = "none";
  }

  // Menü dışına tıklanınca popup kapanır
  window.addEventListener("click", function (e) {
    const popup = document.getElementById("popupMenu");
    const popupMenu = document.querySelector(".popup-menu");
    const hamburger = document.querySelector(".hamburger");

    // Eğer popup açıksa ve tıklama popup'ın içine veya hamburger ikonuna değilse kapat
    if (popup.style.display === "flex" && !popupMenu.contains(e.target) && !hamburger.contains(e.target)) {
      closePopup();
    }
  });

</script>
  </body>
</html>