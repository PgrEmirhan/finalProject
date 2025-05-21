<?php
require 'connect.php';
require 'auth.php';

$id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT user_name, full_name, email, avatar_path, membership_type FROM users WHERE user_id=?");
$stmt->execute([$id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Profilim</title>
  <style>
    body { font-family: sans-serif; max-width: 600px; margin: auto; padding: 20px; }
    label { display: block; margin-top: 10px; }
    input[type="text"], input[type="email"], input[type="file"] { width: 100%; padding: 8px; margin-top: 4px; }
    img { margin-top: 10px; border-radius: 8px; }
    .btn { display: inline-block; margin-top: 15px; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; }
    .danger { background: #dc3545; }
  </style>
</head>
<body>

<h2>Merhaba, <?= htmlspecialchars($user['full_name'] ?: $user['user_name']) ?></h2>
<p><strong>Kullanıcı Adı:</strong> <?= htmlspecialchars($user['user_name']) ?></p>
<p><strong>Üyelik:</strong> <?= strtoupper($user['membership_type']) ?></p>

<?php if ($user['avatar_path']) : ?>
  <img src="<?= htmlspecialchars($user['avatar_path']) ?>" width="120" alt="Avatar">
<?php endif; ?>

<form action="profile_update.php" method="POST" enctype="multipart/form-data">
  <label>Ad Soyad
    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>">
  </label>

  <label>E‑posta
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
  </label>

  <label>Yeni Avatar Yükle
    <input type="file" name="avatar">
  </label>

  <button type="submit" class="btn">Güncelle</button>
</form>

<a href="change_password.php" class="btn">Şifre Değiştir</a>
<a href="login.php" class="btn danger">Çıkış Yap</a>

</body>
</html>
