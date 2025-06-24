<?php
session_start();
require 'connect.php';

$id = $_SESSION['user_id'];

$user_name = $_POST['user_name'] ?? '';
$email = $_POST['email'] ?? '';

// Avatar silme isteği var mı?
$delete_avatar = isset($_POST['delete_avatar']) && $_POST['delete_avatar'] == '1';

// Eski avatar yolunu veritabanından al
$stmt = $pdo->prepare("SELECT avatar_path FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$existing_avatar = $stmt->fetchColumn();

// Yeni avatar yüklendi mi?
$avatar_path = null;
if (!empty($_FILES['avatar']['name'])) {
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif','webp'];
    if (!in_array(strtolower($ext), $allowed)) {
        die("Desteklenmeyen dosya türü.");
    }

    // Eski avatarı varsa sil
    if ($existing_avatar && file_exists($existing_avatar)) {
        unlink($existing_avatar);
    }

    // Yeni avatarı kaydet
    $avatar_path = 'uploads/avatar_' . $id . '.' . $ext;
    move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path);
}

// Avatar silinmek isteniyorsa ama yeni avatar yüklenmemişse
if ($delete_avatar && !$avatar_path) {
    if ($existing_avatar && file_exists($existing_avatar)) {
        unlink($existing_avatar);
    }
    $avatar_path = null;
}

// Veritabanını güncelle
$query = "UPDATE users SET user_name = ?, email = ?";
$params = [$user_name, $email];

if ($avatar_path !== null || $delete_avatar) {
    $query .= ", avatar_path = ?";
    $params[] = $avatar_path;
}

$query .= " WHERE user_id = ?";
$params[] = $id;

$stmt = $pdo->prepare($query);
$stmt->execute($params);

// 🔁 Session güncelle (yeni kullanıcı adı oturumda da görünür)
$_SESSION['user_name'] = $user_name;

// Geri yönlendir
header("Location: profile.php?ok=1");
exit;

?>
