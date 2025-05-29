<?php
require 'connect.php';
require 'auth.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id || !isset($_POST['file_id'])) {
    die("Yetkisiz erişim veya eksik veri.");
}

$file_id = $_POST['file_id'];

$stmt = $pdo->prepare("UPDATE files SET is_archived = 0 WHERE file_id = ? AND user_id = ?");
$stmt->execute([$file_id, $user_id]);

header("Location: archive.php?unarchived=1");
exit;
