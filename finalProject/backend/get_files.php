<?php
session_start();
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

$stmt = $pdo->prepare("SELECT * FROM files");
$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Yüklenen Dosyalar</h3>
<ul>
    <?php foreach ($files as $file): ?>
        <li>
            <strong><?php echo htmlspecialchars($file['file_name']); ?></strong> 
            - <?php echo round($file['file_size'] / 1024, 2); ?> KB 
            <a href="download_file.php?file_id=<?php echo $file['file_id']; ?>">İndir</a>
        </li>
    <?php endforeach; ?>
</ul>

