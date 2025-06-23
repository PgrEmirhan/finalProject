<?php
require 'connect.php';
require 'auth.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Yetkisiz erişim.");
}

$stmt = $pdo->prepare("SELECT user_name, email, membership_type, avatar_path FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="my_data.json"');
echo json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;
?>