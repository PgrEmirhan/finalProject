<?php
session_start();
require 'connect.php'; // Veritabanı bağlantısı

// Kullanıcı giriş yapmış mı?
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Formdan gelen üyelik tipi
$membership = $_POST['membership_type'] ?? '';

// Geçerli üyelik tipleri
$valid_memberships = ['monthly', 'yearly'];

// Geçersiz bilgi kontrolü
if (!in_array($membership, $valid_memberships)) {
    header("Location: settings.php?error=invalid_membership");
    exit;
}
    $stmt = $pdo->prepare("UPDATE users SET membership_type = ? WHERE user_id = ?");
    $success = $stmt->execute([$membership, $user_id]);

    if ($success) {
        // Başarılıysa yönlendir
        if (isset($_GET['redirect'])) {
            header("Location: settings.php?success=updated");            
         } 
    }
exit;
