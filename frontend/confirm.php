<?php
session_start();

require 'connect.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Kullanıcı oturumu bulunamadı.");
}

// upload.php'den gelen üyelik bilgisi
$membership_type = $_POST['membership_type'] ?? 'free';

try {
    $stmt = $pdo->prepare("UPDATE users SET membership_type = :membership_type WHERE user_id = :user_id");
    $stmt->execute([
        ':membership_type' => $membership_type,
        ':user_id' => $user_id
    ]);

    echo "<p>Ödeme başarılı. Üyelik türünüz <strong>$membership_type</strong> olarak güncellendi.</p>";
    echo "<a href='index.php'>Ana Sayfaya Dön</a>";

} catch (PDOException $e) {
    echo "Hata oluştu: " . $e->getMessage();
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
  <style>
    body {
      font-family: 'Inter', sans-serif;
      overflow-x: hidden;
      background: linear-gradient(485deg, lightgray, lightblue, lightpink, lightgreen, lightyellow);
      margin: 0;
      padding: 0;
    }

    .nav-container {
      display: flex;
      justify-content: space-around;
      align-items: center;
      background-color: rgb(255, 255, 255);
      width: 100%;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      font-family: 'Inter', sans-serif;

    }

    .nav-container ul {
      margin-left: 956px;
      list-style-type: none;
      display: flex;
      gap: 15px;
    }

    .nav-container ul a {
      text-decoration: none;
      color: rgb(0, 0, 0);
      padding: 10px;
      font-size: 18px;
      transition: all 0.7s;
      font-weight: bold;
    }

    .success-msg { color: green; }
    .error-msg { color: red; }

    main {
      margin-top: 135px;
      margin-bottom: 80px;
    }

    main h2 {
      font-size: 35px;
      text-align: center;
    }

    #payment-content {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      padding: 10px;
    }

    .payment-image img {
      width: 500px;
      padding: 35px 0;
      margin-left: 35px;
    }

    .stick {
      background-color: black;
      width: 3px; 
      height: 375px;
      margin-right: 250px;
      margin-left: 100px;
    }
    #payment-content {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      padding: 10px;
    }

    .payment-image img {
      width: 500px;
      padding: 35px 0;
      margin-left: 35px;
    }

    .stick {
      background-color: black;
      width: 3px; 
      height: 375px;
      margin-right: 250px;
      margin-left: 100px;
    }

    .wrapper {
      
      font-family: 'Inter', sans-serif;
    }

    .card-selection {
      position: relative;
      width: 340px;
      height: 240px;
      margin: auto;
      margin-bottom: 90px;
      margin-right: 55px;
    }

    .card-stack {
      position: relative;
      width: 100%;
      height: 100%;
    }

    .card-container {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      transition: transform 0.4s ease, z-index 0.4s ease;
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
      cursor: pointer;
      opacity: 0.9;
      color: white;
    }

    .card-container.visa {
      background: linear-gradient(to right, #1a2980, #26d0ce);
      transform: translateX(-20px);
      z-index: 1;
    }

    .card-container.mastercard {
      background: linear-gradient(to right, #ff512f, #dd2476);
      transform: translateX(0);
      z-index: 2;
    }
    .card-container.troy {
  background: linear-gradient(to right, #0bab64, #3bb78f); /* Yeşil tonlu */
  transform: translateX(-20px);
  z-index: 2;
}
    .card-container.selected {
      z-index: 10;
      transform: translateY(-20px) scale(1.05);
      opacity: 1;
      border: 2px solid white;
    }

    .card-logo {
      width: 60px;
    }

    .card-title {
      font-size: 18px;
      margin-top: 10px;
    }

    .chip {
      width: 40px;
      height: 30px;
      background-color: #ccc;
      border-radius: 5px;
      margin: 15px 0;
    }

    .card-info {
      font-size: 18px;
      margin-bottom: 10px;
    }

    .card-footer {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
    }

    .form-container {
      display: flex;
      flex-direction: column;
      gap: 10px;
      width: 300px;
      margin: auto;
    }

    .form-container input[type="text"],
    .form-container input[type="password"],
    .form-container input[type="submit"] {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .form-container input[type="submit"] {
      background-color: black;
      color: white;
      border: none;
      cursor: pointer;
      font-weight: bold;
    }

    footer {
      background-color: black;
      color: white;
      width: 100%;
      padding: 10px;
      text-align: center; 
    }

    footer a {
      text-decoration: none;
      color: white;
    }

    .footer-nav {
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
    }

    .footer-nav ul {
      list-style-type: none;
      padding: 0;
    }

    .footer-nav h3 {
      margin-bottom: 10px;
    }

    @media screen and (max-width: 768px) {
      #payment-content {
        flex-direction: column;
        align-items: center;
      }

      .stick {
        display: none;
      }

      .payment-image img {
        margin-left: 0;
      }
    }

  </style>
</head>
<body>

<header>
  <nav class="nav-container">
    <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px;"></a>
    <ul>
      <li><a href="register.php"><i class="fas fa-user-plus icon"></i> Üye Ol</a></li>
      <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> İletişim</a></li>
    </ul>
  </nav>
</header>
 
<main>
  <div style="text-align: center; margin-top: 80px;">
    <img src="https://cdn-icons-png.flaticon.com/512/845/845646.png" alt="Başarılı" width="120">
    <h2 style="color: green;">Üyeliğiniz Başarıyla Güncellendi!</h2>
    <p>Ödemeniz başarıyla alındı. Artık özel üyelik ayrıcalıklarından yararlanabilirsiniz.</p>
    <p style="font-weight: bold; margin-top: 10px;">Gelişmiş dosya yükleme, hızlı indirme ve reklamsız deneyim sizi bekliyor!</p>
    <a href="upload.php" style="display: inline-block; margin-top: 25px; padding: 12px 30px; background-color: black; color: white; border-radius: 5px; text-decoration: none;">Yükleme ekranına dön</a>
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
      <li><a href=""><b>Telefon: </b> +90 123 456 789</a></li>
      <li><a href=""><b>Email: </b>support@dosyapaylasim.com</a></li> 
    </ul>
  </div> 
  <p align="center">Tüm haklar saklıdır. TE-FS &copy;2025</p>
</footer>

<script>
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