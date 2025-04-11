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
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

$message = "";
$valid_token = false; // FORMU GÖSTERMEYİ KONTROL EDECEK BAYRAK

// URL'den token alınıyor
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Token veritabanında kontrol ediliyor
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $valid_token = true;

        // Formdan gönderim yapıldıysa
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
            $new_password = $_POST['new_password'];

            if (strlen($new_password) < 6) {
                $message = "⚠️ Şifreniz en az 6 karakter olmalıdır.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Şifreyi güncelle, token'ı temizle
                $stmt = $pdo->prepare("UPDATE users SET user_password = ?, reset_token = NULL WHERE reset_token = ?");
                $stmt->execute([$hashed_password, $token]);

                $message = "✅ Şifreniz başarıyla güncellendi. <a href='login.php'>Giriş yap</a>";
                $valid_token = false; // formu tekrar gösterme
            }
        }
    } else {
        $message = "❌ Geçersiz ya da süresi dolmuş bağlantı.";
    }
} else {
    $message = "❌ Token belirtilmedi.";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Şifre Belirle</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f3f3;
            padding: 40px;
        }
        form {
            background: #fff;
            padding: 25px;
            max-width: 400px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        input[type="password"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        .message {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
        }
        .message.success { color: green; }
        .message.error { color: red; }
    </style>
</head>
<body>

    <h2 align="center">Yeni Şifre Belirle</h2>

    <?php if ($valid_token): ?>
        <form method="post">
            <label for="new_password">Yeni Şifreniz:</label>
            <input type="password" name="new_password" required placeholder="Yeni şifrenizi girin">
            <input type="submit" value="Şifreyi Güncelle">
        </form>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="message <?= $valid_token ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

</body>
</html>
