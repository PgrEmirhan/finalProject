<?php
session_start();

require 'connect.php';
// Token varsa URL'den al
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Bu token'la kullanıcıyı veritabanında ara
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Geçersiz veya süresi dolmuş token.");
    }

    // Yeni şifre formu gönderildiyse
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
        $new_password = $_POST['new_password'];

        // Şifreyi hash’le
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Şifreyi güncelle, token’ı sil
        $stmt = $pdo->prepare("UPDATE users SET user_password = ?, reset_token = NULL WHERE reset_token = ?");
        $stmt->execute([$hashed_password, $token]);

        echo "<p style='color: green;'>✔️ Şifreniz başarıyla sıfırlandı. <a href='login.php'>Giriş yap</a></p>";
        exit();
    }

} else {
    die("Token bulunamadı. Lütfen geçerli bir bağlantı kullanın.");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifre Sıfırla</title>
</head>
<body>
    <h2>Yeni Şifre Belirleyin</h2>
    <form method="POST">
        <label for="new_password">Yeni Şifreniz:</label><br>
        <input type="password" name="new_password" id="new_password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
         title="Parola en az 8 karakter olmalı, bir büyük harf, bir küçük harf, bir rakam ve bir özel karakter içermelidir."
         required><br><br>
        <input type="submit" value="Şifreyi Sıfırla">
    </form>
</body>
</html>
