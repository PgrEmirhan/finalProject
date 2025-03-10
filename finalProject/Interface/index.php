<?php
session_start();

$host = 'localhost';
$dbname = 'file_sharing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı hatası: " . $e->getMessage());
}

// Kullanıcı Girişi Kontrolü
if (isset($_POST['login']) && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
    } else {
        echo "Hatalı kullanıcı adı veya şifre!";
    }
}

// Kullanıcı Kayıt İşlemi
if (isset($_POST['register']) && isset($_POST['reg_username']) && isset($_POST['reg_password'])) {
    $reg_username = $_POST['reg_username'];
    $reg_password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT);

    // Kullanıcıyı veritabanına ekle
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$reg_username, $reg_password]);

    $_SESSION['message'] = "Kayıt başarılı! Giriş yapabilirsiniz.";
}

// Dosya Yükleme
if (isset($_POST['upload']) && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // Dosya adı ve yolu
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    if ($fileError === 0) {
        // Dosyanın uzantısını kontrol et
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'docx', 'zip');

        if (in_array($fileExt, $allowed)) {
            if ($fileSize < 10000000) { // Maksimum 10MB
                $fileDestination = __DIR__ . '/uploads/' . uniqid('', true) . '.' . $fileExt;
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Eğer kullanıcı giriş yapmışsa, user_id alınır
                    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
                    // Misafir (guest) yükleme için user_id NULL olmalı
                    $sql = "INSERT INTO files (file_name, file_path, user_id) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$fileName, $fileDestination, $userId]);
                    echo "Dosya başarıyla yüklendi!";
                } else {
                    echo "Dosya yüklenirken bir hata oluştu.";
                }
            } else {
                echo "Dosya çok büyük, lütfen 10MB'dan küçük bir dosya yükleyin.";
            }
        } else {
            echo "Geçersiz dosya formatı!";
        }
    } else {
        echo "Dosya yüklenirken bir hata oluştu.";
    }
}

// Dosya İndirme
if (isset($_GET['action']) && $_GET['action'] == 'download' && isset($_GET['file_id'])) {
    $file_id = $_GET['file_id'];

    // Dosyayı veritabanından al
    $stmt = $pdo->prepare("SELECT * FROM files WHERE ID = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $file_path = $file['file_path'];
        if (file_exists($file_path)) {
            // Dosyayı indir
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit();
        } else {
            echo "Dosya bulunamadı.";
        }
    } else {
        echo "Geçersiz dosya.";
    }
}

// Dosya Silme
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['file_id'])) {
    $file_id = $_GET['file_id'];

    // Dosyayı veritabanından al
    $stmt = $pdo->prepare("SELECT * FROM files WHERE ID = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $file_path = $file['file_path'];
        if (unlink($file_path)) { // Dosya sunucudan sil
            // Dosyayı veritabanından sil
            $delete_stmt = $pdo->prepare("DELETE FROM files WHERE ID = ?");
            $delete_stmt->execute([$file_id]);
            echo "Dosya başarıyla silindi.";
        } else {
            echo "Dosya silinirken bir hata oluştu.";
        }
    } else {
        echo "Dosya bulunamadı!";
    }
}

// Dosya Paylaşma
if (isset($_GET['action']) && $_GET['action'] == 'share' && isset($_GET['file_id'])) {
    $file_id = $_GET['file_id'];

    // Dosyayı veritabanından al
    $stmt = $pdo->prepare("SELECT * FROM files WHERE ID = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $file_name = $file['file_name'];
        $file_path = $file['file_path'];

        // Paylaşılacak URL
        $share_url = "http://localhost/" . $file_path;

        // Paylaşım linkini göster
        echo "Dosya Paylaşım Linki: <a href='$share_url' target='_blank'>$share_url</a>";
    } else {
        echo "Dosya bulunamadı!";
    }
}

// Dosyaları Listeleme (Girişli ve Anonim Dosyalar)
$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id IS NULL");
$stmt->execute();
$anon_files = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $files = [];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Paylaşım Sistemi</title>
</head>
<body>

    <h2>Dosya Paylaşım Sistemi</h2>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- Misafir Kullanıcı Butonu -->
        <h3>Misafir olarak dosya yükleyin:</h3>
        <a href="guest_upload.php">Misafir olarak yükle</a>

        <hr>

        <!-- Kayıt Olma Formu -->
        <h3>Kayıt Ol</h3>
        <form action="" method="POST">
            <label for="reg_username">Kullanıcı Adı:</label>
            <input type="text" name="reg_username" required><br><br>
            <label for="reg_password">Şifre:</label>
            <input type="password" name="reg_password" required><br><br>
            <button type="submit" name="register">Kayıt Ol</button>
        </form>

        <hr>

        <!-- Giriş Yapma Formu -->
        <h3>Giriş Yap</h3>
        <form action="" method="POST">
            <label for="username">Kullanıcı Adı:</label>
            <input type="text" name="username" required><br><br>
            <label for="password">Şifre:</label>
            <input type="password" name="password" required><br><br>
            <button type="submit" name="login">Giriş Yap</button>
        </form>
    <?php else: ?>
        <!-- Kullanıcı Girişi Başarılı -->
        <h3>Merhaba, <?php echo $_SESSION['username']; ?>!</h3>
 
    <!-- Çıkış Butonu -->
    <form action="logout.php" method="POST">
         <button type="submit" name="logout">Çıkış Yap</button>
    </form>

        <!-- Dosya Yükleme Formu -->
        <h3>Dosya Yükle</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="file" required><br><br>
            <button type="submit" name="upload">Dosya Yükle</button>
        </form>

        <hr>

        <h3>Yüklenen Dosyalar</h3>
        <ul>
            <?php foreach ($files as $file): ?>
                <li>
                    <strong><?php echo htmlspecialchars($file['file_name']); ?></strong> 
                    - <?php echo round($file['file_size'] / 1024, 2); ?> KB
                    <br>
                    <a href="?action=download&file_id=<?php echo $file['ID']; ?>">İndir</a> |
                    <a href="?action=delete&file_id=<?php echo $file['ID']; ?>">Sil</a> |
                    <a href="?action=share&file_id=<?php echo $file['ID']; ?>">Paylaş</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</body>
</html>
