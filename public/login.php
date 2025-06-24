<?php
session_start();
 
require 'connect.php';
require 'csrf.php';
// Form gönderildiyse işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

if (isset($_POST['username']) && isset($_POST['password'])) {
    $input_username = trim($_POST['username']);  
    $input_password = $_POST['password'];      
 
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_name = ?");  
    $stmt->execute([$input_username]); 
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if ($user && password_verify($input_password, $user['user_password'])) { 
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
 
        header("Location: upload.php");
        exit();
    } else { 
        $error_message = "Hatalı kullanıcı adı veya şifre!";
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
    <title>Giriş Yap</title>
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com"> 
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link rel="stylesheet" href="assets/login.css?v=1">
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
    <div class="container">
        <h3 align="center" style="font-size: 32px;">Giriş Yap</h3>
        <form action="login.php" method="post"> 
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="uname-icon">
                <input type="text" name="username" id="uname" placeholder="Kullanıcı adınız..." style="width: 100%;" pattern="^[a-zA-Z0-9._]{1,30}$"
                title="Kullanıcı adı yalnızca harf, rakam, nokta (.) ve alt çizgi (_) içerebilir. Boşluk karakteri kullanılamaz." 
                required>
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="pword-icon">
                <input type="password" name="password" id="pword" placeholder="Parolanız..." style="width: 100%;" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
         title="Parola en az 8 karakter olmalı, bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir."
         required>
                <i class="fa-solid fa-eye-slash" id="togglePassword"></i>
            </div>
            <input type="submit" value="Giriş Yap" id="login-btn">
            <a href="forgot_password.php" style="color: red; text-decoration: none;">Parolamı Unuttum</a>
            <?php 
            if (isset($error_message)) {
                echo "<p style='color: red; text-align: center;'>$error_message</p>";
            }
            ?>

            <p align="center">Hesabınız mı yok? <a href="register.php">Kayıt olun!</a> 
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
<script src="assets/login.js?v=1">
</script>

</body>
</html>
