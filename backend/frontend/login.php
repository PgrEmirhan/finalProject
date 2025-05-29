<?php
session_start();
 
require 'connect.php';
 
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
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<nav class="nav-container">
        
        <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px; margin-left: 3px;"></a>
        <ul> 
            <li><a href="register.php">            
            <i class="fas fa-user-plus icon"></i>
            Kayıt Ol</a></li>
            <li><a href="contact.php">         
            <i class="fa-solid fa-envelope"></i>
            İletişim</a></li>
        </ul>
                         <button id="dark-mode-toggle"> 
         <i class="fa-solid fa-moon"></i>
        </nav> 
</header>

<main>
    <div class="container">
        <h3 align="center" style="font-size: 32px;">Giriş Yap</h3>
        <form action="login.php" method="post">
            <div class="kadi-icon">
                <input type="text" name="username" id="kadi" placeholder="Kullanıcı adınız..." style="width: 100%;" required>
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="pword-icon">
                <input type="password" name="password" id="pword" placeholder="Parolanız..." style="width: 100%;" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
         title="Parola en az 8 karakter olmalı, bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir."
         required>
                <i class="fa-solid fa-eye-slash" id="togglePassword"></i>
            </div>
            <input type="submit" value="Giriş Yap" id="giris-btn">
            <a href="forgot_password.php" style="color: red; text-decoration: none;">Parolamı Unuttum</a>
            <?php 
            if (isset($error_message)) {
                echo "<p style='color: red; text-align: center;'>$error_message</p>";
            }
            ?>

            <p align="center">Hesabım yok. <a href="register.php">Kayıt ol</a> ya da</p>
        </form>

        <p align="center">Farklı bir hesapla giriş yap</p>
        <div class="hesaplar">
            <a href="#"><i class="fa-brands fa-google"></i></a>
            <a href="#"><i class="fa-brands fa-linkedin"></i></a>
            <a href="#"><i class="fa-brands fa-github"></i></a>
            <a href="#"><i class="fa-brands fa-facebook"></i></a>
        </div>
    </div>
</main>

<footer>  
    <div class="footer-nav"> 
        <ul>
        <a href="#"><h3>HIZLI BAĞLANTILAR</h3></a>
        <li><a href="index.php">Anasayfa</a></li> 
        <li><a href="register.php">Kayıt ol</a></li>  
        <li><a href="contact.php">İletişim</a></li>
        </ul>        <ul>
        <a href="#"><h3>YASAL BİLGİLER</h3></a>
        <li><a href="">Kullanım Koşulları </a></li>
        <li><a href="">Gizlilik Politikası </a></li>
        <li><a href="">Çerez Politikası</a></li> 
        </ul>
 <ul>
        <a href="#"><h3>SOSYAL MEDYA</h3></a>
        <li><a href="">Facebook </a></li>
        <li><a href="">X</a></li>
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
});

// Butona tıklanınca dark mode aç/kapat
document.getElementById('dark-mode-toggle').addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');

  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled'); // aktif halde sakla
  } else {
    localStorage.setItem('darkMode', 'disabled'); // kapalı olarak sakla
  }
});


</script>

</body>
</html>
