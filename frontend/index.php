 <?php
    require 'connect.php'; 
    session_start();
    $shareLink = '';  
    $user_id = null; // Kullanıcı ID'si boş, kullanıcı girişine göre güncellenecek

    // Dosya yükleme işlemi
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


    // Veritabanından tüm dosyaları çekme
    $files = [];
    $sql = "SELECT * FROM files";  
    $stmt = $pdo->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $files[] = $row;
    }

$currentTimestamp = time(); 

// Önce geçerliliği dolmuş dosyaları al
$stmt = $pdo->prepare("SELECT * FROM files WHERE expiry_time < ? AND is_guest = 1");
$stmt->execute([$currentTimestamp]);
$expiredFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dosya sisteminden sil
foreach ($expiredFiles as $file) {
    if (file_exists($file['file_path'])) {
        unlink($file['file_path']); // Dosyayı sunucudan sil
    }
}

// Ardından veritabanından sil
$stmt = $pdo->prepare("DELETE FROM files WHERE expiry_time < ? AND is_guest = 1");
$stmt->execute([$currentTimestamp]);

    // Dosya silme işlemi
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

    // Dosya paylaşma işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['share_file'])) {
         $file_id_to_share = $_POST['share_file'];

        // Kullanıcıya ait dosya sorgulanıyor
        $stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ? AND (user_id = ? OR is_guest = 1)");
        $stmt->execute([$file_id_to_share, $user_id]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($file) { 
            // Dosya paylaşım linki oluşturuluyor
            $shareLink = "http://localhost/finalProject/frontend/uploads/" . basename($file['file_path']);
            $uploadMessage = "<p class='success-msg'>Dosya başarıyla paylaşılabilir: <a href='$shareLink' target='_blank'>$shareLink</a></p>";
        } else {
            $uploadMessage = "<p class='error-msg'>Dosya bulunamadı veya yetkiniz yok.</p>";
        }
    }

    // Dosyaları veritabanından çekme
    try {
        $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? OR is_guest = 1");  
        $stmt->execute([$user_id]); // BUNU EKLEMELİSİN
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
    }

    ?> 

    <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dosya paylaşım sistemi</title>
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
<!-- NAVIGATION BAR -->
<nav class="nav-container">
  <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px;" id="logo"></a>

  <!-- NORMAL MENÜ (büyük ekranlar için) -->
  <ul class="nav-links">
    <li><a href="register.php"><i class="fas fa-user-plus icon"></i> Üye Ol</a></li>
    <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> İletişim</a></li> <!-- DARK MODE BUTTON --> 
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
                    <input type="file" name="file" id="fileInput" /> 

                </form>

                <button class="upload-btn" onclick="uploadFile()">Dosya Yükle</button>
 
                <div class="file-list">
                    <h3 align="center">Yüklediğiniz Dosyalar:</h3>
                 <?php foreach ($files as $file): ?>
                    <form method="post"  action="index.php" style="margin-bottom: 10px;" enctype="multipart/form-data">
                        <input type="hidden" name="file_id" value="<?php echo $file['file_id']; ?>">

                        <strong>                                        <label style="cursor: pointer;">                
                <?= htmlspecialchars($file['file_name']) ?> (<?= $file['file_size'] ?> bayt)                          </label>

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

                </div> <br>
            <h2 align="center" class="cards-title"><i class="fas fa-check-circle"></i>
            Neden bizi tercih etmelisiniz?</h2>
            <div class="cards">
            <div class="card1"><i class="fa-solid fa-share-from-square" style="font-size: 48px; display: flex;
                align-items: center; justify-content: center;"></i> 
                <h3 align="center">Kolay ve Hızlı Dosya Paylaşımı
                </h3>
                <p align="center">Dosya paylaşımı hiç bu kadar kolay olmamıştı! Sadece birkaç tıklama ile dosyalarınızı yükleyin, özelleştirin ve başkalarıyla paylaşın. Sürükle-bırak yöntemi ile dosyalarınızı kolayca yükleyebilir ve paylaşabilirsiniz. Sistemimiz, her türlü dosya türünü destekler ve size güvenli, hızlı bir paylaşım deneyimi sunar.
                </p>
            </div>
            <div class="card2">
                <i class="fa fa-upload" style="font-size: 48px; display: flex;
                align-items: center; justify-content: center;"></i>
                <h3 align="center">Misafir Kullanıcılar için Ücretsiz Yükleme
                </h3>
                <p align="center">Hesap oluşturmanıza gerek yok! Misafir kullanıcılar da dosyalarını yükleyebilir, 24 saat boyunca kalıcı olacak şekilde paylaşabilir ve özelleştirebilirler. 24 saat sonunda dosyanız otomatik olarak silinir, ancak üyelik ile dosyalarınızı süresiz tutabilirsiniz.
                </p>
            </div>
            <div class="card3"> 
                <i class="fa fa-cloud-upload-alt" style="font-size: 48px; display: flex;
                align-items: center; justify-content: center;"></i>
                <h3 align="center">Süresiz Dosya Saklama ve Ekstra Özellikler
                </h3>
                <p align="center">Üye olduğunuzda, dosyalarınızın süresiz olarak saklanması sağlanır. Ayrıca, indir, sil ve paylaş gibi ek özelliklere erişebilirsiniz. Üyeler ayrıca dosyalarına özel linkler oluşturabilir ve paylaşabilir. Hesabınızla, her zaman kontrol sizde olur!
                </p>
            </div>
            <div class="card4">
                <i class="fa fa-crown" style="font-size: 48px; display: flex;
                align-items: center; justify-content: center"></i>
                <h3 align="center">Premium Üyelikle Daha Fazla Avantaj
                </h3>
                <p align="center">Premium üyelik ile dosya paylaşımını bir üst seviyeye taşıyın. Premium kullanıcılar, daha yüksek dosya yükleme limitlerine, özel dosya özelleştirme seçeneklerine ve sınırsız indirme hızına sahip olacaklar. Dosyalarınız her zaman güvende olacak ve sadece siz yönetebileceksiniz.
                </p>
            </div>
            </div> 
            <h2 align="center" style="font-size: 32px; margin-top: 35px;"><i class="fas fa-cogs"></i>
            Sistemimizin Çalışma Prensibi</h2>
            <div class="system-articles">
            <p> <b>Misafir Kullanıcılar:</b> Üyelik gerektirmeden dosyalarınızı yükleyebilirsiniz. 24 saat boyunca dosyanız aktif olacak ve istediğiniz zaman indirebilirsiniz. 
            </p>
            <p> <b> Üyelik Sistemi: </b>Üye olarak daha fazla avantaj elde edin! Süresiz dosya saklama, dosyalarınız üzerinde tam kontrol ve çok daha fazlası.</p>
            <p>  <b>Premium Özellikler: </b>Premium üyelikle dosya boyutu limitlerini aşabilir, dosya özelleştirme ve paylaşım seçeneklerini en üst seviyeye çıkarabilirsiniz.</p>
            <ul>
                <li>Kullanıcı Dostu Arayüz
                Uygulamamız, kullanıcı dostu bir arayüze sahiptir. Basit sürükle-bırak yöntemi ile dosyalarınızı yükleyebilir ve çok kısa sürede paylaşmaya başlayabilirsiniz. Ayrıca, dosya yönetimi çok kolaydır; yükledikten sonra her zaman dosyalarınızı indirebilir, silebilir veya yeni kullanıcılarla paylaşabilirsiniz.</li>
            <li>        
                Güvenli ve Hızlı
                Verilerinizin güvenliği bizim için çok önemli. Dosyalarınız en yüksek güvenlik önlemleriyle saklanır ve yalnızca sizin belirlediğiniz kişilerle paylaşılır. Ayrıca, dosya yükleme ve indirme hızları oldukça hızlıdır, böylece zaman kaybetmeden işlemlerinizi gerçekleştirebilirsiniz.</p>
            </li></ul>
                <b>Hangi Üyelik Sizin İçin Uygun?</b>
        <br>
        <ul><li><b>Aylık Üyelik:</b> Eğer kısa vadede tüm premium özelliklerden yararlanmak istiyorsanız, aylık üyelik tam size göre.
        </li>
        <li><b>Yıllık Üyelik:</b> Uzun vadeli bir çözüm arıyorsanız, yıllık üyelikle hem daha fazla avantaj elde edebilir hem de ödeme konusunda tasarruf sağlayabilirsiniz.
        </li>
        <li><b>Özel Premium (Takım/İşletme): </b>Eğer birden fazla kişiye dosya paylaşımı yapmanız gerekiyorsa veya işletmenizin özel ihtiyaçları varsa, özel premium üyelik sizin biçin ideal.</li>
        </ul>

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
        <li><i class="fa-solid fa-check"></i> 5GB Bulut depolama alanı </li>
        <li><i class="fa-solid fa-check"></i> Reklamsız ve hızlı kullanım deneyimi </li>
        <li><i class="fa-solid fa-check"></i> 10 MB’a kadar dosya yükleme limiti </li>
        <li><i class="fa-solid fa-check"></i> Yüksek öncelikli dosya indirme </li>
        <li><i class="fa-solid fa-check"></i> Temel dosya gizlilik ayarları </li>
        <li><i class="fa-solid fa-check"></i> E-posta üzerinden destek </li>
      </ul>
      <input type="hidden" name="membership_type" value="free">
      <a href="register.php"><button class="uyelik-btn">Şimdi Geçiş Yap</button></a> 
    </div>
    <div class="price-card"> 
      <h3 style="font-size: 32px;"> Aylık Üyelik </h3>   
        <h4 style="font-size: 34px; width: 100%; height: 30px; ">Fiyat: 199,99 TL</h4> 
      <br>
      <ul>
        <li><i class="fa-solid fa-check"></i> 15GB Bulut depolama alanı </li>
        <li><i class="fa-solid fa-check"></i> Gelişmiş dosya arşivleme ve filtreleme</li>
        <li><i class="fa-solid fa-check"></i> 1 GB'a kadar tek dosya yükleme</li>
        <li><i class="fa-solid fa-check"></i> Erişim limiti ayarlama</li>
        <li><i class="fa-solid fa-check"></i> Şifreli paylaşım bağlantıları oluşturma</li>
        <li><i class="fa-solid fa-check"></i> Hızlı geri bildirim destek hattı</li>
      </ul>
      <input type="hidden" name="membership_type" value="Monthly">
      <a href="register.php"><button class="uyelik-btn">Şimdi Geçiş Yap</button></a>
    </div>

    <div class="price-card"> 
      <h3 style="font-size: 32px;"> Yıllık Üyelik </h3>    
      <h4 style="font-size: 34px; color: black; width: 100%; height: 30px; ">Fiyat: 499,99 TL</h4>
      <ul>
        <li><i class="fa-solid fa-check"></i> 1TB Bulut depolama alanı </li>  
        <li><i class="fa-solid fa-check"></i> 5 GB'a kadar tek dosya yükleme</li>
        <li><i class="fa-solid fa-check"></i> Gelişmiş dosya arşivleme ve filtreleme</li>
        <li><i class="fa-solid fa-check"></i> Sınırsız dosya yükleme ve paylaşım hakkı</li>
        <li><i class="fa-solid fa-check"></i> Link süresi ve erişim limiti ayarlama</li>
        <li><i class="fa-solid fa-check"></i> Şifreli paylaşım bağlantıları oluşturma</li>
        <li><i class="fa-solid fa-check"></i> Reklamsız şekilde dosya yükleme ve paylaşım</li> 
      </ul>
      <form action="payment.php" method="POST">
      <input type="hidden" name="membership_type" value="Yearly">
      <button type="submit" class="uyelik-btn">Şimdi Geçiş Yap</button>
    </form>
    </div>

  </div>

  <div class="premium-advantages">
    <p align="left">
      <i class="fas fa-gem"></i>
      <b>Neden Premium Olmalısınız?</b><br><br>
      Premium üyelik ile dosyalarınızı daha güvenli, daha hızlı ve daha esnek bir şekilde yönetebilirsiniz. Reklamsız kullanım, özel paylaşım seçenekleri, daha büyük dosya limitleri ve öncelikli teknik destek gibi ayrıcalıklardan faydalanarak deneyiminizi en üst seviyeye çıkarabilirsiniz. Gelişmiş güvenlik, kişisel gizlilik ayarları ve profesyonel kullanım imkanı ile dijital dünyada fark yaratın.
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
        <script> 
    const words = ["YÜKLEYİN","PAYLAŞIN", "YÖNETİN"];
    let index = 0;
    const wordElement = document.getElementById("word");
    let currentWord = '';
    let letterIndex = 0;

    function typeLetter() {
        if (letterIndex < currentWord.length) {
            wordElement.textContent += currentWord.charAt(letterIndex);
            letterIndex++;
            setTimeout(typeLetter, 100); 
        } else {
            setTimeout(() => { 
                index = (index + 1) % words.length;
                currentWord = words[index];
                letterIndex = 0;
                wordElement.textContent = ''; 
                typeLetter();  
            }, 1500); 
        }
    }
    
    currentWord = words[index];
    typeLetter();  
    
    function triggerFileInput() {
        document.getElementById('fileInput').click();
    }
    
    function uploadFile() {
        var fileInput = document.getElementById('fileInput');
        var file = fileInput.files[0];
        if (!file) {
            alert('Lütfen bir dosya seçin!');
            return;
        }

        var formData = new FormData();
        formData.append('file', file);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php', true);

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                var percent = (e.loaded / e.total) * 100;
                document.getElementById('progress-bar').style.width = percent + '%';
                document.getElementById('progress-bar').textContent = Math.round(percent) + '%';
            }
        });

        xhr.onload = function() {
            if (xhr.status === 200) {
                alert("Dosya başarıyla yüklendi!");
                location.reload();  
            } else {
                alert("Dosya yükleme sırasında bir hata oluştu.");
            }
        };

        xhr.send(formData); 
    }
    
    var dropArea = document.getElementById('drop-area');
    dropArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropArea.classList.add('hover');
    });

    dropArea.addEventListener('dragleave', function() {
        dropArea.classList.remove('hover');
    });

    dropArea.addEventListener('drop', function(e) {
        e.preventDefault();
        dropArea.classList.remove('hover');
        var file = e.dataTransfer.files[0];
        document.getElementById('fileInput').files = e.dataTransfer.files;
    });
    
function confirmDelete(fileId) {
    if (confirm('Emin misiniz? Bu dosya kalıcı olarak silinecek?')) {
 
        const formData = new FormData();
        formData.append('delete_file', fileId); 

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                alert('Dosya başarıyla silindi.');
                location.reload();
            } else {
                alert('Dosya silinirken bir hata oluştu.');
            }
        };
        xhr.send(formData);
    }
}

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

// Butona tıklanınca dark mode aç/kapat ve logoyu güncelle
document.getElementById('dark-mode-toggle-desktop').addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
 
  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled');
  } else {
    localStorage.setItem('darkMode', 'disabled');
  }
});
// Butona tıklanınca dark mode aç/kapat ve logoyu güncelle
document.getElementById('dark-mode-toggle-mobile').addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
 
  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('darkMode', 'enabled');
  } else {
    localStorage.setItem('darkMode', 'disabled');
  }
});
  const banner   = document.getElementById('cookie-banner');
  const acceptBtn = document.getElementById('accept-cookies');

  // Daha önce onay verildiyse banner'ı gizle
  if (localStorage.getItem('tefs-cookies-accepted') === 'yes') {
    banner.style.display = 'none';
  }

  // Kabul Et butonuna tıklanınca onayı kaydet ve gizle
  acceptBtn.addEventListener('click', () => {
    localStorage.setItem('tefs-cookies-accepted', 'yes');
    banner.style.display = 'none';
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