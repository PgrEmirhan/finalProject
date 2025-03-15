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

    // Kullanıcı adı daha önce alınmış mı kontrol et
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$input_username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "Bu kullanıcı adı zaten alınmış!";
    } else {
        // Şifreyi hashleyerek veritabanına ekle
        $hashed_password = password_hash($input_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$input_username, $hashed_password]);

        // Kayıt başarılı, anasayfaya yönlendir
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol</title>
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
        }

        .form-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease-in-out;
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
        @media (max-width: 600px) {
            .form-container {
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }

            .form-container input, .form-container button {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="form-container">
        <h2>Kayıt Ol</h2>
        <form action="register.php" method="POST">
            <input type="text" name="username" placeholder="Kullanıcı Adı" required>
            <input type="password" name="password" placeholder="Şifre" required>
            <button type="submit">Kayıt Ol</button>
        </form>
        <div class="link-container">
            <a href="login.php">Zaten hesabınız var mı? Giriş yapın.</a>
        </div>
        <div style="text-align: center; margin-top: 10px;">
            <a href="index.php">Ana Sayfaya Dön</a>
        </div>
    </div>
</body>
</html>
