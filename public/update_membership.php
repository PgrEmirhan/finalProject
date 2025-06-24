<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$membership = $_POST['membership_type'] ?? '';
$valid_memberships = ['free', 'monthly', 'yearly'];

if (!in_array($membership, $valid_memberships)) {
    header("Location: settings.php?error=invalid_membership");
    exit;
}

// Şu anki üyelik kontrolü
$stmt = $pdo->prepare("SELECT membership_type FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$current = $stmt->fetchColumn();

if ($membership === $current) {
    header("Location: settings.php?error=same_membership");
    exit;
}

// Güncelleme işlemi
$stmt = $pdo->prepare("UPDATE users SET membership_type = ? WHERE user_id = ?");
$success = $stmt->execute([$membership, $user_id]);

if ($success) {
    header("Location: settings.php?success=updated");
    exit;
} else {
    header("Location: settings.php?error=update_failed");
    exit;
}
?>
