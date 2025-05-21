<?php
require 'connect.php';
require 'auth.php';

$id = $_SESSION['user_id'];

$full_name = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';

// Avatar yükleme işlemi
$avatar_path = null;
if (!empty($_FILES['avatar']['name'])) {
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $avatar_path = 'uploads/avatar_' . $id . '.' . $ext;
    move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path);
}

// Veritabanını güncelle
$query = "UPDATE users SET full_name = ?, email = ?" . ($avatar_path ? ", avatar_path = ?" : "") . " WHERE user_id = ?";
$params = [$full_name, $email];
if ($avatar_path) $params[] = $avatar_path;
$params[] = $id;

$stmt = $pdo->prepare($query);
$stmt->execute($params);

header("Location: profile.php?ok=1");
exit;
