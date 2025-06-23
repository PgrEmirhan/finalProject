<?php
session_start(); 

require 'connect.php';   
require 'csrf.php';
$message = "";

// PHPMailer kütüphaneleri
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $input_email = $_POST['email'];
    if (!filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Geçersiz e-posta adresi!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$input_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $reset_token = bin2hex(random_bytes(16));
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
            $stmt->execute([$reset_token, $input_email]);

            $reset_link = "http://localhost/finalProject/public/reset_password.php?token=" . $reset_token;

            $mail = new PHPMailer(true);
            try {
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'emirhankot423@gmail.com'; // Gmail adresin
                $mail->Password   = 'njof ieco vzkw gqyy'; // Gmail'den aldığın uygulama şifresi
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('emirhankot423@gmail.com', 'Parola Sıfırlama');
                $mail->addAddress($input_email); // Kullanıcının e-postası

                $mail->isHTML(true);
                $mail->Subject = 'Parola Sıfırlama Bağlantısı';
                $mail->Body    = "Parolanızı sıfırlamak için <a href='$reset_link'>buraya tıklayın</a>.<br>Veya bu bağlantıyı kopyalayın: $reset_link";

                $mail->send();
                $message = "✔️ E-posta adresinize sıfırlama bağlantısı gönderildi!";
            } catch (Exception $e) {
                $message = "❌ E-posta gönderilemedi. Hata: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Bu e-posta adresi sistemde kayıtlı değil.";
        }
    }
}
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
  <link rel="stylesheet" href="assets/forgot.css?v=1">
  </head>
<body>
<header>
<!-- NAVIGATION BAR -->
<nav class="nav-container">
  <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px;" id="logo"></a>

  <!-- NORMAL MENÜ (büyük ekranlar için) -->
  <ul class="nav-links">
<li><a href="register.php"><i class="fas fa-user-plus icon"></i> Üye Ol</a></li>
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
<li><a href="register.php"><i class="fas fa-user-plus icon"></i> Üye Ol</a></li>
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

    <?php if ($message): ?>
        <p style="color: <?= strpos($message, '✔️') !== false ? 'green' : 'red' ?>;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" action="forgot_password.php">   
       <h2>Parolanızı mı unuttunuz?</h2>

         <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label for="email">Kayıtlı E-posta Adresiniz:</label>
        <br>
        <input type="email" name="email" required placeholder="ornek@mail.com" id="email">
        <br><br>
        <input type="submit" value="Sıfırlama Linki Gönder" id="btn">
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
