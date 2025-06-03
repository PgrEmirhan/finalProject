<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require 'connect.php';
$user_id = $_SESSION['user_id'];

// Eğer formdan üyelik tipi gelmişse:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['membership_type'])) {
    $membershipType = $_POST['membership_type']; // "monthly" veya "yearly"

    // Burada ödeme işlemi yapılmış varsayılıyor (ödeme entegrasyonu buraya gelir)

    // Kullanıcının üyeliğini güncelle
    $stmt = $pdo->prepare("UPDATE users SET membership_type = ? WHERE user_id = ?");
    $stmt->execute([$membershipType, $user_id]);

    echo "<p>Ödemeniz başarıyla alındı! Üyeliğiniz $membershipType olarak güncellenmiştir.</p>";
    echo "<a href='upload.php'>Ana sayfaya dön</a>";
} else {
    echo "<p>Geçersiz işlem.</p>";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Ödeme Sayfası</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&family=Ubuntu&family=Roboto:wght@400;700&display=swap" rel="stylesheet"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link rel="stylesheet" href="assets/payment.css">
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

        <form class="form-container" action="confirm.php" method="POST">
          <input type="hidden" name="card_type" id="card-type" value="visa"><!--
          <input type="hidden" name="card_type" id="card-type" value="mastercard">
          <input type="hidden" name="card_type" id="card-type" value="truy">!-->
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
<script>
  // Karttaki bilgileri güncelleyen fonksiyon
  function updateCard(input, fieldType) {
    const value = input.value.trim();

    const fields = {
      'card-number': formatCardNumber(value),
      'name': value || 'Ad Soyad',
      'expiry': formatExpiry(value)
    };

    const ids = {
      'card-number': ['show-card-number', 'show-card-number-mastercard', 'show-card-number-troy'],
      'name': ['show-name', 'show-name-mastercard', 'show-name-troy'],
      'expiry': ['show-expiry', 'show-expiry-mastercard', 'show-expiry-troy']
    };

    ids[fieldType].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.innerText = fields[fieldType];
    });

    if (fieldType === 'card-number') input.value = formatCardNumber(value);
    if (fieldType === 'expiry') input.value = formatExpiry(value);
  }

  function formatCardNumber(val) {
    return val.replace(/\D/g, '').slice(0, 16).replace(/(.{4})/g, '$1 ').trim() || '**** **** **** ****';
  }

  function formatExpiry(val) {
    let cleaned = val.replace(/\D/g, '').slice(0, 4);
    if (cleaned.length > 2) cleaned = cleaned.slice(0, 2) + '/' + cleaned.slice(2);
    return cleaned || 'AA/YY';
  }

  // Kart seçildiğinde dönüş açılarını ve kart türünü ayarlayan fonksiyon
  function selectCard(selectedCard, type) {
    const cards = document.querySelectorAll('.card-container');
    const angles = ['rotate(160deg)', 'rotate(380deg)'];
    let angleIndex = 0;

    cards.forEach(card => {
      card.classList.remove('selected');

      if (card === selectedCard) {
        card.style.transform = 'translateY(-20px) scale(1.05)';
      } else {
        card.style.transform = angles[angleIndex];
        angleIndex++;
      }
    });

    selectedCard.classList.add('selected');
    document.getElementById('card-type').value = type;
  }

  document.addEventListener('DOMContentLoaded', function () {
    const selectedCard = document.querySelector('.card-container.selected');
    const cards = document.querySelectorAll('.card-container');
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
  });
  function validateName(input) {
  input.value = input.value.replace(/[^a-zA-ZğüşöçİĞÜŞÖÇ\s]/g, '');
}
function validateCVV(input) {
  input.value = input.value.replace(/\D/g, '');
}

</script>
</body>
</html>