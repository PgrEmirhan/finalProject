<?php
session_start();
require 'connect.php';

$user_id = $_SESSION['user_id'];

$user_name = $_POST['user_name'] ?? '';
$email = $_POST['email'] ?? '';

$delete_avatar = isset($_POST['delete_avatar']) && $_POST['delete_avatar'] == '1';

$stmt = $pdo->prepare("SELECT avatar_path FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$existing_avatar = $stmt->fetchColumn();

$avatar_path = null;
if (!empty($_FILES['avatar']['name'])) {
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png', 'gif','webp'];
    if (!in_array(strtolower($ext), $allowed)) {
        die("Desteklenmeyen dosya türü.");
    }

    if ($existing_avatar && file_exists($existing_avatar)) {
        unlink($existing_avatar);
    }

    $avatar_path = 'uploads/avatar_' . $user_id . '.' . $ext;
    move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path);
}

if ($delete_avatar && !$avatar_path) {
    if ($existing_avatar && file_exists($existing_avatar)) {
        unlink($existing_avatar);
    }
    $avatar_path = null;
}

$query = "UPDATE users SET user_name = ?, email = ?";
$params = [$user_name, $email];

if ($avatar_path !== null || $delete_avatar) {
    $query .= ", avatar_path = ?";
    $params[] = $avatar_path;
}

$query .= " WHERE user_id = ?";
$params[] = $user_id;

$stmt = $pdo->prepare($query);
$stmt->execute($params);

$_SESSION['user_name'] = $user_name;

header("Location: profile.php?ok=1");
exit;

?>