 <?php
    session_start(); 
    require 'connect.php'; 
    require 'csrf.php';
    $shareLink = '';  
    $user_id = null; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];
    $fileSize = $file['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'docx', 'zip'];

    if ($fileError === 0) {
        if (!in_array($fileExt, $allowed)) {
            echo "<p class='error-msg'>Geçersiz dosya türü.</p>";
        }   else {
            $newFileName = uniqid('', true) . '.' . $fileExt;
            $fileDestination = __DIR__ . '/uploads/' . $newFileName;

            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                $uploadedAt = time();
                $expiryTime = isset($user_id) ? '0000-00-00 00:00:00' : $uploadedAt + 86400;
                $is_guest = isset($user_id) ? 0 : 1;

                $stmt = $pdo->prepare("INSERT INTO files (file_name, file_path, user_id, is_guest, uploaded_at, expiry_time, file_size) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $fileName,
                    $fileDestination,
                    $user_id ?? null,
                    $is_guest,
                    $uploadedAt,
                    $expiryTime,
                    $fileSize
                ]);

                echo "<p class='success-msg'>Dosya başarıyla yüklendi.</p>";
            } else {
                echo "<p class='error-msg'>Dosya taşınamadı.</p>";
            }
        }
    } else {
        echo "<p class='error-msg'>Yükleme sırasında hata oluştu.</p>";
    }
}

    $files = [];
    $sql = "SELECT * FROM files";  
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $files[] = $row;
    }

$currentTimestamp = time(); 

$stmt = $pdo->prepare("SELECT * FROM files WHERE expiry_time < ? AND is_guest = 1");
$stmt->execute([$currentTimestamp]);
$expiredFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($expiredFiles as $file) {
    if (file_exists($file['file_path'])) {
        unlink($file['file_path']);
    }
}

$stmt = $pdo->prepare("DELETE FROM files WHERE expiry_time < ? AND is_guest = 1");
$stmt->execute([$currentTimestamp]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
 
        $file_id_to_delete = $_POST['delete_file'];
        $stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ? AND (is_guest = 1)");
        $stmt->execute([$file_id_to_delete]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($file) { 
            if (unlink($file['file_path'])) { 
                $stmt = $pdo->prepare("DELETE FROM files WHERE file_id = ?");
                $stmt->execute([$file_id_to_delete]);
                echo "<p class='success-msg'>Dosya başarıyla silindi.</p>";
            } else {
                echo "<p class='error-msg'>Dosya silinirken bir hata oluştu.</p>";
            }
        } else {
            echo "<p class='error-msg'>Dosya bulunamadı.</p>";
        }
    }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['share_file'])) {
         $file_id_to_share = $_POST['share_file'];

        $stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ? AND (user_id = ? OR is_guest = 1)");
        $stmt->execute([$file_id_to_share, $user_id]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
  
        if ($file) { 
            $shareLink = "http://localhost/finalProject/public/uploads/" . basename($file['file_path']);
            $uploadMessage = "<p class='success-msg'>Dosya başarıyla paylaşılabilir: <a href='$shareLink' target='_blank'>$shareLink</a> <button type='button' id='copyBtn' onclick='copyLink()'>Kopyala</button></p>";
        } else {
            $uploadMessage = "<p class='error-msg'>Dosya bulunamadı veya yetkiniz yok.</p>";
        }
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? OR is_guest = 1");  
        $stmt->execute([$user_id]); 
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
    }

    ?> 

    <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">    
          <meta charset="UTF-8">
          <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Anasayfa</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
        <link rel="stylesheet" href="assets/index.css">
        </head>
         <body>
        <header>
<nav class="nav-container">
  <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px;" id="logo"></a>

  <ul class="nav-links">
    <li><a href="register.php"><i class="fas fa-user-plus icon"></i> Üye Ol</a></li>
    <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> İletişim</a></li> <!-- DARK MODE BUTTON --> 
  </ul>       
        <button id="dark-mode-toggle-desktop">
         <i class="fa-solid fa-moon"></i>
        </button> 

        <div class="hamburger" onclick="openPopup()">☰</div>
</nav>

<div class="popup-overlay" id="popupMenu">
  <div class="popup-menu"> 
    <ul>
      <li><a href="register.php"><i class="fas fa-user-plus icon"></i> Üye Ol</a></li>
      <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> İletişim</a></li> 
      <li>
        <button id="dark-mode-toggle-mobile">
         <i class="fa-solid fa-moon"></i>
        </button>
</li>
    </ul>
  </div>
</div>
<br>
        </header>
        <main>  
        <div align="center" class="slogan">
            <h1 style="font-size: 36px;">DOSYALARINIZI GÜVENLE <span id="word"></span></h1>
        </div>
                <div class="guest_upload">
                <h2>İlk Dosyanızı Yükleyin!</h2>      
                <img src="images/file-upload.png" width="240">

                <?php if (isset($uploadMessage)) echo $uploadMessage; ?>

                <div id="drop-area" onclick="triggerFileInput()">
                    Dosyayı Buraya Sürükleyin veya Seçmek için Tıklayın
                </div>

                <div id="progress-container">
                    <div id="progress-bar">0%</div>
                </div>
                <br>

                <form id="uploadForm" action="index.php" method="POST" enctype="multipart/form-data" style="display:none;">    
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="file" name="file" id="fileInput" />  
                </form>

                <button type="button" class="upload-btn" onclick="uploadFile()">Dosya Yükle</button>
 
                <div class="file-list" id="file-list">
                    <h3 align="center">Yüklediğiniz Dosyalar:</h3>
                 <?php foreach ($files as $file): ?>
                    <form method="post"  action="index.php" style="margin-bottom: 10px;" enctype="multipart/form-data">
                        <input type="hidden" name="file_id" value="<?php echo $file['file_id']; ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <strong>                                        
                        <label style="cursor: pointer;">                
                <?= htmlspecialchars($file['file_name']) ?> (<?= $file['file_size'] ?> bayt)                          
              </label> 
</strong>
        <button type="button" onclick="if(confirmDelete(<?= $file['file_id']; ?>)){ window.location='index.php?delete_file=<?= $file['file_id']; ?>'; }">
          Sil
        </button>|
        <button type="submit" name="share_file" value="<?php echo $file['file_id']; ?>">Paylaş</button>|
        <a href="uploads/<?= basename($file['file_path']) ?>" download>
        <button type="button">İndir</button>
        </a>
        </form>
        <?php endforeach; ?>

                </div> <br><br>
            <h2 align="center" class="cards-title"><i class="fas fa-check-circle"></i>
            Neden bizi tercih etmelisiniz?</h2>
            <div class="cards">
            <div class="card1"><i class="fa-solid fa-share-from-square" style="font-size: 48px; display: flex;
                align-items: center; justify-content: center;"></i> 
                <h3 align="center">Kolay ve Hızlı Dosya Paylaşımı
                </h3>
                <p align="center"><strong>Dosya paylaşımını hiç bu kadar kolay görmediniz!</strong><br>
                Sistemimizle, sürükle-bırak yöntemi ile tek tıkla dosya yükleyebilir ve başkalarıyla hızlıca paylaşabilirsiniz. Bir dosya yüklemek, sadece birkaç saniye sürer. Her türlü dosya türünü destekliyoruz ve güvenli bir aktarım sağlıyoruz. 
                </p>
            </div>
            <div class="card2">
                <i class="fa fa-upload" style="font-size: 48px; display: flex;
                align-items: center; justify-content: center;"></i>
                <h3 align="center">Misafir Kullanıcılar için Ücretsiz Yükleme
                </h3>
               <p align="center"><strong>Üye olmadan dosya yükleme imkanı!</strong><br>
      Misafir kullanıcılar, herhangi bir üyelik oluşturmak zorunda kalmadan dosyalarını yükleyebilir. Yükledikleri dosyalar 24 saat boyunca aktif kalır. Ücretsiz dosya saklama ve gelişmiş özelliklere erişim için basit ve hızlı bir çözüm!
            </p>
            </div>
            <div class="card3"> 
                <i class="fa fa-cloud-upload-alt" style="font-size: 48px; display: flex;
                align-items: center; justify-content: center;"></i>
                <h3 align="center">Süresiz Dosya Saklama ve Ekstra Özellikler
                </h3>
                <p align="center"><strong>Dosyalarınızı tek bir panelde yönetin.</strong><br>
                Üyeler, kendi özel kullanıcı panellerine sahip olur. Buradan dosyalarınızı görüntüleyebilir, düzenleyebilir ve yönetebilirsiniz. Ayrıca profil ayarları, dosya arşivleme ve özelleştirilmiş paylaşımlar yapabilirsiniz.
                </p>
            </div>
            <div class="card4">
                <i class="fa fa-crown" style="font-size: 48px; display: flex;
                align-items: center; justify-content: center"></i>
                <h3 align="center">Ücretli Üyeliklerle Daha Fazla Avantaj
                </h3>
                <p align="center"><strong>Dosyalarınızı istediğiniz kadar saklayın ve yönetin!</strong><br>
Aylık ve yıllık üyelik seçeneklerimiz, size dosya yönetiminde tam kontrol sağlar. Üye olarak, dosyalarınızı süresiz saklayabilir ve istediğiniz zaman erişebilirsiniz.
                </p>
            </div>
            </div> 
            <h2 align="center" style="font-size: 32px; margin-top: 35px;"><i class="fas fa-cogs"></i>
            Sistemimizin Çalışma Prensibi</h2>
            <div class="system-articles">
Sistemimiz, kullanıcılarımıza kolay ve güvenli dosya paylaşımı imkanı sunan bir platformdur. Misafir kullanıcılar, herhangi bir üyelik gerektirmeden dosya yükleyebilir ve bu dosyaları başkalarıyla paylaşabilir. Yükledikleri dosyalar, 24 saat boyunca aktif kalır ve hızlı bir şekilde paylaşım yapılabilir. Bu, acil dosya paylaşımı yapanlar için ideal bir çözüm sunar. Ancak, üye kullanıcılar için daha fazla avantaj bulunmaktadır. Üyelik sayesinde dosyalarınız süresiz olarak saklanır ve istediğiniz zaman erişebilirsiniz. Ayrıca, üyeler kendilerine özel bir kullanıcı paneline sahip olur ve burada tüm dosyalarını kolayca yönetebilir. Bu panel üzerinden dosyalarınızı filtreleyebilir, arşivleyebilir ve daha düzenli bir şekilde saklayabilirsiniz. <br>

Üye olarak, dosya paylaşımınızı da daha güvenli ve kişiselleştirilmiş hale getirebilirsiniz. Örneğin, belirli e-posta adreslerine özel dosya paylaşımları yapabilir, dosyalarınıza parola koruması ekleyebilir ve geçerlilik süresi belirleyebilirsiniz. Bu özellikler, dosyalarınızın sadece belirli kişilerle paylaşılmasını ve güvenli bir şekilde kontrol edilmesini sağlar. Ayrıca, üyeler gelişmiş paylaşım seçenekleri ve dosya yönetim araçlarına da erişim sağlar. Böylece, dosyalarınızı daha verimli bir şekilde organize edebilir ve paylaşım sürecini daha pratik hale getirebilirsiniz.

            </p>
            </div>

            <div class="premium-container">
  <h2 align="center" style="font-size: 32px;">
    <i class="fa-solid fa-money-bill"></i>
    Üyelik Planları
     <i class="fa-solid fa-money-bill"></i>
  </h2> 
  <div class="premium-price">

    <div class="price-card">
      
      <h3 style="font-size: 32px;"> Temel Üyelik </h3>   
      <h4 style="font-size: 34px; color: black; width: 100%; height: 30px; ">Fiyat: 0,00 TL</h4> 
      <ul>
        <li><i class="fa-solid fa-check"></i> 10 MB’a kadar dosya yükleme limiti </li>
        <li><i class="fa-solid fa-check"></i> Yüksek öncelikli dosya indirme </li>
        <li><i class="fa-solid fa-check"></i> Temel dosya gizlilik ayarları </li>
        <li><i class="fa-solid fa-check"></i> E-posta üzerinden destek </li>
      </ul>
      <form action="register.php" method="POST">
      <input type="hidden" name="membership_type" value="free">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <button type="submit" class="uyelik-btn">Şimdi Kayıt Ol</button>
    </form>    
    </div>
    <div class="price-card"> 
      <h3 style="font-size: 32px;"> Aylık Üyelik </h3>   
        <h4 style="font-size: 34px; width: 100%; height: 30px; ">Fiyat: 199,99 TL</h4> 
      <br>
      <ul>
        <li><i class="fa-solid fa-check"></i> Gelişmiş dosya arşivleme ve filtreleme</li>
        <li><i class="fa-solid fa-check"></i> 1 GB'a kadar tek dosya yükleme</li>
        <li><i class="fa-solid fa-check"></i> Erişim limiti ayarlama</li>
        <li><i class="fa-solid fa-check"></i> Şifreli paylaşım bağlantıları oluşturma</li>
        <li><i class="fa-solid fa-check"></i> Hızlı geri bildirim destek hattı</li>
      </ul>
      <form action="register.php" method="POST">
      <input type="hidden" name="membership_type" value="monthly">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <button type="submit" class="uyelik-btn">Şimdi Kayıt Ol</button>
    </form>    
  </div>

    <div class="price-card"> 
      <h3 style="font-size: 32px;"> Yıllık Üyelik </h3>    
      <h4 style="font-size: 34px; color: black; width: 100%; height: 30px; ">Fiyat: 299,99 TL</h4>
      <ul>
        <li><i class="fa-solid fa-check"></i> 5 GB'a kadar tek dosya yükleme</li>
        <li><i class="fa-solid fa-check"></i> Gelişmiş dosya arşivleme ve filtreleme</li>
        <li><i class="fa-solid fa-check"></i> Sınırsız dosya yükleme ve paylaşım hakkı</li>
        <li><i class="fa-solid fa-check"></i> Link süresi ve erişim limiti ayarlama</li>
        <li><i class="fa-solid fa-check"></i> Şifreli paylaşım bağlantıları oluşturma</li>
      </ul>
      <form action="register.php" method="POST">
      <input type="hidden" name="membership_type" value="yearly">   
       <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <button type="submit" class="uyelik-btn">Şimdi Kayıt Ol</button>
    </form>
    </div>

  </div>

  <div class="premium-advantages">
    <p align="left">
      <i class="fas fa-gem"></i>
      <b>Neden Üyeliğe Geçmelisiniz?</b><br><br>
Üyelik, dosya paylaşımını ve yönetimini çok daha kolay ve güvenli hale getirir. İşte üyeliğinizle elde edeceğiniz avantajlar:

Süresiz Dosya Saklama: Yüklediğiniz dosyalar sürekli olarak saklanır, dilediğiniz zaman erişebilirsiniz.

Kişisel Kullanıcı Paneli: Dosyalarınızı tek bir yerden yönetebilir, düzenleyebilir ve özelleştirebilirsiniz.

Gelişmiş Paylaşım Seçenekleri: Dosyalarınızı belirli e-posta adreslerine özel paylaşabilir, parola koruması ve geçerlilik süresi gibi güvenlik önlemleri alabilirsiniz.

Filtreleme ve Arşivleme: Yüklediğiniz dosyaları daha verimli bir şekilde filtreleyebilir ve arşivleyebilirsiniz.

Kolay Yönetim: Kullanıcı paneliniz üzerinden tüm dosyalarınızı hızlıca erişebilir ve yönetebilirsiniz.

Üyelik, hem dosya güvenliği hem de dosya yönetimini daha verimli hale getirir. Hızlı, kolay ve güvenli dosya paylaşımı ve yönetimi için şimdi üye olun!

    </p>
  </div>
  <br>
</div>        
</main>
        <!-- Çerez Bildirimi Başlangıcı -->
<div id="cookie-banner" style="
    position:fixed; left:0; right:0; bottom:0; 
    background:#1f2937; color:#fff; padding:1rem;
    display:flex; flex-direction:column; gap:.5rem;
    align-items:center; z-index:9999; font-family:sans-serif;">
  <span>
    Bu sitede deneyiminizi iyileştirmek için çerezler kullanıyoruz. 
    Detaylar için <a href="legal/cookie-policy.html" style="color:#7dd3fc;">Çerez Politikası</a>’nı inceleyin.
  </span>
  <button id="accept-cookies" style="
      background:#10b981; border:none; color:#fff; 
      padding:.5rem 1rem; cursor:pointer; border-radius:.25rem;">
    Kabul Et
  </button>
</div>

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
        <script src="assets/index.js?v=1"> 

        </script>
        </body>
        </html>