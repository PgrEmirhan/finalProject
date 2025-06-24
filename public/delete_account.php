<?php
session_start(); 
require 'connect.php'; 

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Yetkisiz erişim.");
}

$stmt = $pdo->prepare("SELECT file_path FROM files WHERE user_id = ?");
$stmt->execute([$user_id]);
$files = $stmt->fetchAll();

foreach ($files as $file) {
    if (file_exists($file['file_path'])) {
        unlink($file['file_path']);
    }
}

$pdo->prepare("DELETE FROM files WHERE user_id = ?")->execute([$user_id]);

$pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$user_id]);

session_destroy();
header("Location: goodbye.php");
exit;
?>