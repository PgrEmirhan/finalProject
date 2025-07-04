<?php
session_start();
require 'connect.php';
require 'csrf.php';

$message = "";
$token = $_GET['token'] ?? $_POST['token'] ?? null;

if (!$token) {
    die("Token bulunamadı. Lütfen geçerli bir bağlantı kullanın.");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Geçersiz veya süresi dolmuş token.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) { 

    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET user_password = ?, reset_token = NULL WHERE reset_token = ?");
    $stmt->execute([$hashed_password, $token]);

    echo "<p style='color: green;'>✔️ Şifreniz başarıyla sıfırlandı. <a href='login.php'>Giriş yap</a></p>";
    exit;
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
  <link rel="stylesheet" href="assets/reset.css?v=2">
  </head>
<body>
  <header>
    <nav class="nav-container">
      
    <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px; margin-top:0px; margin-right: 111px;" id="logo"></a>
      <ul>  
        <li><a href="contact.php" style="margin-right: 1px;">         
           <i class="fas fa-envelope icon"></i>
          İletişim</a></li>
      </ul>
           <button id="dark-mode-toggle"> 
         <i class="fa-solid fa-moon" ></i>
      </button>  
  </header>
  <main>
    <h2  align="center">Yeni Parola Belirleyin</h2>
    <form method="POST">    
         <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"> <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <label for="new_password">Yeni Parolanız:</label><br>
        <input type="password" name="new_password" id="new_password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
         title="Parola en az 8 karakter olmalı, bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir."
         required><br><br>
        <input type="submit" value="Parolayı Sıfırla" placeholder="Parolanız..." id="btn">
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
        <script src="assets/reset.js?v=1">
    </script>

</body>
</html>
