<?php
session_start();
require 'connect.php';
require 'csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {
        $error = "Yeni şifre ve onay eşleşmiyor.";
    } else {
        // Kullanıcı eski şifreyi veritabanından çek
        $stmt = $pdo->prepare("SELECT user_password FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user && password_verify($oldPassword, $user['user_password'])) {
            // Eski şifre doğru, yeni şifreyi hash'le ve güncelle
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET user_password = ? WHERE user_id = ?");
            $stmt->execute([$newHash, $userId]);

            $success = "Şifreniz başarıyla değiştirildi.";
        } else {
            $error = "Mevcut şifre yanlış.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
<title>Şifre Değiştir</title>
</head>
<body>
<h2>Şifre Değiştir</h2>

<?php if (!empty($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <p style="color: green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="POST" action="">    
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <label>Mevcut Parola:
        <input type="password" name="old_password" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$">
    </label><br><br>

    <label>Yeni Parola:
        <input type="password" name="new_password" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$">
    </label><br><br>

    <label>Yeni Parola (Tekrar):
        <input type="password" name="confirm_password" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$">
    </label><br><br>

    <button type="submit">Parola Güncelle</button>
</form>

<a href="profile.php">Profil sayfasına dön</a>
</body>
</html>
