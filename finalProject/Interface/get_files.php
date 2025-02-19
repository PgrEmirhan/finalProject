<?php
$host = 'localhost';
$dbname = 'file_sharing';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı hatası: " . $e->getMessage());
}

$sql = "SELECT * FROM files";
$stmt = $pdo->query($sql);

$files = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $files[] = $row;
}

header('Content-Type: application/json');
echo json_encode($files);
?>