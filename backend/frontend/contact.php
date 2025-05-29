 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/contact.css?v=1">
</head>
<body>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <header>
    <nav class="nav-container">
    <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px; margin-top:0px; margin-left: 16px;"></a>
      <ul>
        <li><a href="register.php" style=" margin-right:20px;">                       
          <i class="fas fa-user-plus icon"></i>
          Üye Ol</a></li>               
           <button id="dark-mode-toggle"> 
         <i class="fa-solid fa-moon"></i>
      </ul> 
      </button>
    </nav>  
  </header>
  <main>    
    <div class="container">
      <h3 align="center" style="font-size: 32px;">Destek ve Tavsiye İçin Bizimle İletişime Geçin</h3>
      <form action="contact-mail.php" method="post"> 
        <div class="kadi-icon">
      <input type="text" name="kadi" id="kadi" placeholder="adınız..." style="width: 100%;"> <i class="fa-solid fa-user"></i></div>
        <div class="kadi-icon">
      <input type="text" name="email" id="email" placeholder="mail adresiniz..." style="width: 100%;"> <i class="fa-solid fa-envelope"></i></div>
        <div>
        <textarea name="message" id="message" cols="54" rows="9" placeholder="mesajınız..."></textarea>
        </div>
          <input type="submit" value="Gönder" id="gonder-btn">
          <p align="center"> * Tekrar eden mesajlar spama düşecektir *  </p>
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
        <li><a href="">Kullanım Koşulları </a></li>
        <li><a href="">Gizlilik Politikası </a></li>
        <li><a href="">Çerez Politikası</a></li> 
        </ul>
        <ul>
        <a href="#"><h3>SOSYAL MEDYA</h3></a>
        <li><a href="">Facebook </a></li>
        <li><a href="">Twitter</a></li>
        <li><a href="">Instagram</a></li> 
        </ul>
        <ul>
        <a href="#"><h3>İLETİŞİM BİLGİLERİ </h3></a>
        <li><a href=""><b>Telefon: </b> +90 123 456 789
        </a></li>
        <li><a href=""><b>Email: </b>support@dosyapaylasim.com
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