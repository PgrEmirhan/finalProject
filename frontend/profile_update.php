<?php
require 'connect.php';
require 'auth.php';

$id = $_SESSION['user_id'];

$user_name = $_POST['user_name'] ?? '';
$email = $_POST['email'] ?? '';
$membership_type = $_POST['membership_type'] ?? 'free'; // yeni eklendi

// Avatar yükleme işlemi
$avatar_path = null;
if (!empty($_FILES['avatar']['name'])) {
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $avatar_path = 'uploads/avatar_' . $id . '.' . $ext;
    move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path);
}
    
// Veritabanını güncelle
$query = "UPDATE users SET user_name = ?, email = ?, membership_type=?" . ($avatar_path ? ", avatar_path = ?" : "") . " WHERE user_id = ?";
$params = [$user_name, $email, $membership_type];
if ($avatar_path) $params[] = $avatar_path;
$params[] = $id;
// Tüm işlemleri yaptıktan sonra:
if (isset($_GET['redirect']) && $_GET['redirect'] == 1) {
    header("Location: payment.php?new=$membership_type");
    exit;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);

header("Location: profile.php?ok=1");
exit;
    