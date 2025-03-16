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
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$input_username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($input_password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

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
        /* Animasyon ve stil ayarları */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            opacity: 0;
            animation: fadeInPage 1s forwards;
        }

        .form-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            transform: translateY(-50px);
            opacity: 0;
            animation: slideIn 1s ease-in-out forwards;
        }

        h2 {
            text-align: center;
            color: #4CAF50;
            font-size: 36px;
            margin-bottom: 30px;
        }

        .form-container input {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 2px solid #ddd;
            font-size: 16px;
            opacity: 0;
            animation: fadeInInput 1s ease-in-out forwards;
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
            opacity: 0;
            animation: fadeInButton 1s ease-in-out forwards;
        }

        .form-container button:hover {
            background-color: #45a049;
        }

        .link-container {
            margin-top: 15px;
            text-align: center;
        }

        .link-container a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }

        .link-container a:hover {
            text-decoration: underline;
        }

        .back-to-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-home a {
            color: #4CAF50;
            font-size: 16px;
            text-decoration: none;
        }

        .back-to-home a:hover {
            text-decoration: underline;
        }

        @keyframes fadeInPage {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes slideIn {
            0% { transform: translateY(-50px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        @keyframes fadeInInput {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInButton {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
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
