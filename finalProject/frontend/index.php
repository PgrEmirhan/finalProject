<?php
$host = 'localhost'; 
$dbname = 'file_sharing'; 
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}

$shareLink = '';  
$user_id = null; // Kullanıcı ID'si boş, kullanıcı girişine göre güncellenecek

// Dosya yükleme işlemi
if (isset($_FILES['file'])) {
    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileError = $_FILES['file']['error'];
    $fileSize = $_FILES['file']['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'docx', 'zip'];

    // Dosya hatası yoksa, yükleme işlemini başlat
    if ($fileError === 0) {
        if (!in_array($fileExt, $allowedExtensions)) {
            echo "<p class='error-msg'>Geçersiz dosya türü. Yalnızca jpg, jpeg, png, gif, pdf, txt, docx, zip dosyalarına izin verilmektedir.</p>";
            exit;
        }

        $newFileName = uniqid('', true) . '.' . $fileExt;
        $fileDestination = 'uploads/' . $newFileName;

        // Dosyayı belirtilen dizine taşıma işlemi
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            $currentTimestamp = time();
            $expireTimestamp = $currentTimestamp + 86400;  // Dosyanın geçerliliği 1 gün (86400 saniye)

            // Dosya veritabanına kaydediliyor
            $stmt = $pdo->prepare("INSERT INTO files (file_name, file_path, is_guest, uploaded_at, expiry_time) VALUES (:file_name, :file_path, 1, :uploaded_at, :expiry_time)");
            $stmt->execute([
                ':file_name' => $fileName,
                ':file_path' => $fileDestination,
                ':uploaded_at' => $currentTimestamp,
                ':expiry_time' => $expireTimestamp
            ]);
            echo "<p class='success-msg'>Dosya başarıyla yüklendi.</p>";
        }  
    } else {
        echo "<p class='error-msg'>Dosya yüklenirken bir hata oluştu.</p>";
    }
}

// Veritabanından tüm dosyaları çekme
$files = [];
$sql = "SELECT * FROM files";  
$stmt = $pdo->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $files[] = $row;
}

// Geçerliliği dolmuş dosyaları silme
$currentTimestamp = time(); 
$sql = "DELETE FROM files WHERE expiry_time < :current_time AND is_guest = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':current_time' => $currentTimestamp]);

// Dosya silme işlemi
if (isset($_GET['delete_file'])) {
    $file_id_to_delete = $_GET['delete_file'];
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
if (isset($_GET['share_file'])) {
    $file_id_to_share = $_GET['share_file'];

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya paylaşım sistemi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    
    <style>
        body{
        font-family: 'Inter', sans-serif;   
        overflow-x: hidden;
        } 
        .file-list {
            margin-top: 20px;
        }
        .file-list p {
            margin: 10px 0;
        }
        .file-list a {
            margin-right: 10px;
            text-decoration: none;
            color: #4CAF50;
        }   
        .nav-container{ 
        display: flex;
        justify-content: space-around;
        align-items: center;
        background-color: rgb(255, 255, 255);
        color: white;
        width: 100%; 
        font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        top: 0;
        left: 0;    
        position: fixed;
        z-index: 1;
        }
        .nav-container ul{
        margin-left: 956px;
        list-style-type: none; 
        display:flex;
        gap: 15px; 
    }
        .nav-container ul a{ 
        text-decoration: none;
        color: rgb(0, 0, 0); 
        padding: 10px; 
        font-size: 18px;
        transition: all 0.7s;
        background-color: rgb(255, 255, 255);
    } 
    
    .success-msg { color: green; }
        .error-msg { color: red; }
        
    .promotion {
                text-align: center;
                margin-top: 92px;
            }

            h1 { 
                color: #000000;
            }

            #word {
                color: #000000;
                font-weight: bold;
                display: inline-block;
            }

            .typing-effect {
                display: inline-block;
                border-right: 3px solid #333;  
                padding-right: 5px;
                white-space: nowrap;
                overflow: hidden;
            }

            @keyframes blink {
                50% {
                    border-color: transparent;
                }
            } 
    .satin-btn{
    margin-top: 10px;
    border: none;
    padding: 15px 45px;
    background-color: lightgoldenrodyellow;
    border-radius: 10px; 
    cursor: pointer;
    }
    .cards {
    display: flex;
    justify-content: space-evenly;
    column-gap: 25px; 
    }
    .cards p{
    font-size: 15px;
    }
    .cards h3{
    font-size: 18px;
    }

    .cards .card1, .card2, .card3, .card4 {
    align-items: center;
    background-color: rgb(243, 243, 243);
    width: 250px;
    height: 320px;
    color: #333333;
    border-radius: 3500px 3500px 950px 950px;
    padding: 15px; 
    box-shadow: 9px 15px 1px 0px black;   
    transition: transform 0.4s ease, box-shadow 0.3s ease;   
    }
    .cards h3{
    color: #2C3E50;
    }

    .cards .card1:hover, .card2:hover, .card3:hover, .card4:hover {
    transform: translateY(-10px);  
    box-shadow: 9px 25px 1px 0px black;   
    }

    .fa{ 
    border-radius: 100%;
    width: 50px;
    margin: 0px auto;
    cursor: pointer;
    transition: all 0.45s;
    z-index: 1;
    background-color: transparent;
    color: 2C3E50 ;
    }
    .fa:hover{ 
    z-index: 1; 
    cursor: pointer; 
    border-radius: 100%;
    width: 50px;
    margin: 0px auto; 
    }   
    .guest_upload{
    display: flex;
    flex-direction: column;
    justify-content: center; 
    align-items: center; 
    }
    .guest_upload h2{
    margin-top: -2px;
    }

    .upload-file{
    padding: 15px;
    border: 2px dashed;
    margin-bottom: 15px;
    cursor: pointer;
    } 
    
    .premium-price {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-top: 40px;
    } 

    .price-card {
    background-color: #fff;  
    padding: 25px 20px; 
    width: 100%;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15); 
    border-radius: 12px;  
    transition: all 0.3s ease;  
    text-align: center;
    border: 1px solid #000000;  
    }

    .premium-price .price-card h4 {
    font-size: 50px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    }

    .premium-price .price-card ul {
    list-style-type: none;
    padding: 0;
    font-size: 14px;
    color: #ffffff;
    margin-bottom: 20px;
    }

    .price-card ul li {
    margin-bottom: 8px;
    }
 
    .premium-price.price-card:hover { 
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);  
    }
    .premium-price .price-card:hover h4 {
    color: #fff;  
    }
 
    .price-card:nth-child(1) {
    background-color: #66bcf1;  
    border-radius: 15px;
    }
 
    .price-card:nth-child(2) {
    background-color: #66bcf1;  
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);  
    }


    .price-card:nth-child(3) {
    background-color: #66bcf1;  
    border-radius: 15px;

    }
    .premium-advantages{
    margin-top: 15px;
    padding: 0px 10px;
    background-color: #fff;  
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);  
    border-radius: 12px;  
    transition: all 0.3s ease;  
    text-align: center;
    border: 1px solid #000000;  
    }
    footer {
    background-color: black;
    color: white;
    width: 100%;  
    padding: 10px;
    position: absolute;  
    bottom: auto;  
    left: 0;
    right: 0;  
    text-align: center; 
    }

    footer a{
    text-decoration: none;
    color: white;

    }
    .footer-nav{
    display: flex;
    justify-content: space-around;

    }
    .footer-nav ul{  list-style-type: none;
    }

            #progress-container {
                width: 30.5%;
                height: 10px;
                padding-top:10px;
                padding-bottom:10px;
                padding-left:10px;
                background-color: lightgreen;
                margin-top: 10px;
                border-radius: 5px;
            }

            #progress-bar {
                width: 0;
                height: 100%;
                background-color: #4CAF50;
                text-align: center;
                color: white;
                line-height: 10px;
                border-radius: 5px;
            }
            .upload-btn{
            width: 31.5%;
            padding: 15px 5px;
            border: 1px solid;
            border-radius: 5px;
            background: linear-gradient(15deg, rgb(205, 248, 205), lightyellow, rgb(227, 246, 252),rgb(182, 184, 187), rgb(235, 220, 222));
            cursor: pointer;
            font-weight: bolder;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            font-size: 15px;
            }
            .premium-container h2{
            margin-top: 55px;
            }
          #drop-area{
            border: 2px dashed;
            padding: 8px;
            text-align: center;
          }
          .system-articles{
            text-align:left;
          }
    </style>
    </head>
    <body>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <header>
        <nav class="nav-container">
        
        <a href="index.php"><img src="images/logo.png" alt="" style="width: 80px;"></a>
        <ul> 
            <li><a href="register.php">            
            <i class="fas fa-user-plus icon"></i>
            Üye Ol</a></li>
            <li><a href="contact.php">         
            <i class="fa-solid fa-envelope"></i>
            İletişim</a></li>
        </ul>
        </nav> 
    </header>
    <main>  
    <div align="center" class="promotion">
        <h1 style="font-size: 36px;">DOSYALARINIZI GÜVENLE <span id="word"></span></h1>

            <div class="guest_upload">
            <h2>İlk Dosyanızı Yükleyin!</h2>
            <img src="images/upload.png" alt=""  width="200">

            <?php if (isset($uploadMessage)) echo $uploadMessage; ?>

            <div id="drop-area" onclick="triggerFileInput()">
                Dosyayı Buraya Sürükleyin veya Seçmek için Tıklayın
            </div>

            <div id="progress-container">
                <div id="progress-bar">0%</div>
            </div>
            <br>

            <form id="uploadForm" action="guest_upload.php" method="POST" enctype="multipart/form-data" style="display:none;">
                <input type="file" name="file" id="fileInput" />
            </form>

            <button class="upload-btn" onclick="uploadFile()">Dosya Yükle</button>

            <div class="file-list">
                <h3>Yüklediğiniz Dosyalar:</h3>
                <?php
                    foreach ($files as $file) { 
                        $filePath = 'uploads/' . basename($file['file_path']);
                        echo '<p>' . $file['file_name'] . ' - ';
                        echo '<a href="#" onclick="confirmDelete(' . $file['file_id'] . ')">Sil</a> | ';
                        echo '<a href="?share_file=' . $file['file_id'] . '">Paylaş</a> | ';
                        echo '<a href="' . $filePath . '" download >İndir</a></p>';
                    }
                ?>
            </div>
        <h2 align="center" style="font-size: 32px;"><i class="fas fa-check-circle"></i>

        Neden bizi tercih etmelisiniz?</h2>
        <div class="cards">
        <div class="card1">
            <i class="fa fa-share-alt" style="font-size: 48px;"></i>
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
        <div class="premium-container">
        <h2 align="center" style="font-size: 32px;">
            <i class="fas fa-credit-card"></i>
            Premium Üyelik Seçenekleri ve Fiyatlandırma
        </h2> 
        <div class="premium-price">
        <div class="price-card">
            <i class="fas fa-calendar-alt"></i>

            Aylık Premium Üyelik
            Fiyat: 19,99 TL / Ay
            Avantajlar:
            <ul>
            <li>Sınırsız dosya saklama</li>
            
            <li>Yüksek boyutlu dosya yükleme limiti</li>
            
            <li>Dosya indirme ve paylaşma hızında öncelik</li>
            
            <li>Özel dosya şifreleme ve güvenlik seçenekleri</li>
            
            <li>24/7 destek</li>
        </ul>
        <a href="premium.html"><button class="satin-btn">Satın al</button></a>
        </div>
        <div class="price-card">
            <i class="fas fa-calendar"></i>

            Yıllık Premium Üyelik
            Fiyat: 199,99 TL / Yıl (Aylık 16,66 TL)
            <br>
            Avantajlar:
            
            <ul><li>1 yıl boyunca sınırsız dosya saklama</li>
            
            <li>Tüm premium avantajlarına tam erişim</li>
            
            <li>Yüksek dosya boyutu limiti</li>
            
            <li>Dosya paylaşım linklerinde özelleştirme</li>
            
            <li>Ücretsiz özel alan ve dosya şifreleme</li>
            
            <li>24/7 destek</li></ul>
            
        <button class="satin-btn">Satın al</button>
            </div>
        <div class="price-card">
            <i class="fas fa-users"></i>

            Özel Premium (Takım/İşletme)
            Fiyat: 499,99 TL / Yıl (Fiyatlandırma, kullanıcı sayısına göre değişir)
            
            Avantajlar:
            
        <ul><li>Sınırsız dosya saklama ve yüksek dosya yükleme sınırı</li>
            
        <li>Paylaşım linklerinde marka özelleştirme (Logo, özel alan adı, vb.)</li>
            
        <li>İşletmeye özel bulut depolama alanı</li>
            
        <li>Grup yönetimi ve dosya erişim izinleri</li>
            
        <li>Hızlı indirme ve paylaşım önceliği</li>
            
            <li>7/24 özel destek hattı</li></ul>
            
        <button class="satin-btn">Satın al</button>
            </div>
        </div>
            <div class="premium-advantages"><p align="left">
            <i class="fas fa-gem"></i>

                <b>Neden Premium Olmalısınız?</b>

                Premium üyelik ile sistemimizin tüm olanaklarına sınırsız erişim sağlayabilir, dosyalarınızı güvenle saklayarak hızlı bir şekilde paylaşabilir ve sürekli erişim hakkı elde edebilirsiniz. Ayrıca, dosya güvenliğiniz ve paylaşımlarınız üzerinde tam kontrol sahibi olursunuz. Premium üyeler, yükledikleri dosyaları sonsuza kadar saklayabilir, dosya boyutu sınırları daha geniştir, dosyalarını şifreleyebilir ve sadece istedikleri kişilere erişim izni verebilirler. Ayrıca, herhangi bir sorunuz olduğunda Premium üyeler için öncelikli 7/24 destek hattı sağlanmaktadır.</p></div>
        </div> 
        <h2 align="center" style="font-size: 32px; margin-top: 15px;"><i class="fas fa-cogs"></i>
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
        <li><a href="">LinkedIn</a></li>
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
    if (confirm('Emin misiniz? Bu dosya kalıcı olarak silinecek.')) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'index.php?delete_file=' + fileId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                alert('Dosya başarıyla silindi.');
                location.reload(); 
            } else {
                alert('Dosya silinirken bir hata oluştu.');
            }
        };
        xhr.send();
    }
}
    </script>
    </body>
    </html>

