<?php
session_start();

require 'connect.php';

$message = "";

// PHPMailer kütüphaneleri
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $input_email = trim($_POST['email']);

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

            $reset_link = "http://localhost/finalProject/frontend/reset_password.php?token=" . $reset_token;

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
    <nav class="nav-container">
      
    <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px; margin-top:0px; margin-right: 111px;"></a>
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
    <h2>Parolanızı mı unuttunuz?</h2>

    <?php if ($message): ?>
        <p style="color: <?= strpos($message, '✔️') !== false ? 'green' : 'red' ?>;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" action="forgot_password.php">
        <label for="email">Kayıtlı E-posta Adresiniz:</label><br>
        <input type="email" name="email" required placeholder="ornek@mail.com">
        <br><br>
        <input type="submit" value="Sıfırlama Linki Gönder">
    </form></main>
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
