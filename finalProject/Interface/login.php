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

if (isset($_POST['username']) && isset($_POST['password'])) {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Kullanıcıyı veritabanından sorgula
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_name = ?");
    $stmt->execute([$input_username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($input_password, $user['user_password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];

        // Giriş başarılı, upload'a yönlendir
        header("Location: upload.php");
        exit();
    } else {
        echo "Hatalı kullanıcı adı veya şifre!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <style>
     /* Ana Sayfa Tasarımı */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f7fa;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    width: 100vw;
    box-sizing: border-box; /* Tüm öğeler için box modelini düzenler */
    overflow: hidden; /* Sayfa taşmasını engeller */
}

/* Form container */
.form-container {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 500px;
    box-sizing: border-box; /* İçerik alanını düzgün hizalar */
    transition: transform 0.3s ease-in-out;
    min-height: 400px; /* Minimum yükseklik */
}

.form-container:hover {
    transform: scale(1.05);
}

h2 {
    text-align: center;
    color: #4CAF50;
    font-size: 28px;
    margin-bottom: 25px;
}

.form-container input {
    width: 100%;
    padding: 15px;
    margin: 10px 0;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
    box-sizing: border-box; /* Padding dahil genişlik hesaplama */
}

.form-container input:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
}

.form-container button {
    width: 100%;
    padding: 15px;
    background-color: #4CAF50;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.form-container button:hover {
    background-color: #45a049;
}

.form-container button:active {
    transform: scale(0.98);
}

.form-container a {
    text-align: center;
    display: block;
    margin-top: 15px;
    color: #4CAF50;
    text-decoration: none;
    font-size: 16px;
}

.form-container a:hover {
    text-decoration: underline;
}

/* Responsive Tasarım */
@media (max-width: 1024px) {
    body {
        padding: 0 20px; /* Sayfanın kenarlarına biraz boşluk bırak */
        justify-content: flex-start;
    }

    .form-container {
        padding: 20px;
        max-width: 90%;
        min-height: 400px;
    }

    h2 {
        font-size: 24px;
    }

    .form-container input,
    .form-container button {
        font-size: 14px;
        padding: 12px;
    }
}

@media (max-width: 768px) {
    .form-container {
        padding: 20px;
        max-width: 90%;
        min-height: 350px;
    }

    h2 {
        font-size: 22px;
    }

    .form-container input,
    .form-container button {
        font-size: 14px;
        padding: 12px;
    }

    .form-container a {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    body {
        padding: 0 10px; /* Mobilde kenarlarda boşluk bırak */
    }

    .form-container {
        padding: 15px;
        max-width: 100%;
        min-height: 300px;
    }

    h2 {
        font-size: 20px;
    }

    .form-container input,
    .form-container button {
        font-size: 14px;
        padding: 10px;
    }

    .form-container a {
        font-size: 12px;
    }
}

    </style>
</head>
<body>
<div class="form-container">
    <h2>Giriş Yap</h2>
    <form action="login.php" method="POST">
        <input type="text" name="username" placeholder="Kullanıcı Adı" required>
        <input type="password" name="password" placeholder="Şifre" required>
        <button type="submit">Giriş Yap</button>
    </form>

    <div class="link-container">
        <a href="register.php">Hesabınız yok mu? Kayıt olun.</a>
    </div>

    <div class="back-to-home">
        <a href="index.php">Ana Sayfaya Dön</a>
    </div>
</div>

<script>
    // Formu submit etme
    document.getElementById("loginForm").addEventListener("submit", function(event) {
        event.preventDefault();  // Formun hemen gönderilmesini engeller

        // Animasyonu başlat
        document.querySelector('.form-container').classList.add('transition-container');

        // PHP'nin işlemi tamamlamasına izin vermek için 1 saniye bekle
        setTimeout(() => {
            this.submit();  // Formu normal şekilde gönder
        }, 1000);  // 1 saniye sonra formu gönder
    });
</script>
</body>
</html>
