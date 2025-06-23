<?php
session_start();
require 'connect.php';
$user_id = $_SESSION['user_id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->execute([$user_id]);
$files = $stmt->fetchAll();

foreach ($files as $file) {
    $name = htmlspecialchars($file['file_name']);
    $size = round($file['file_size'] / 1024, 2);
    echo "<div>$name ($size KB)</div>";
}
?>