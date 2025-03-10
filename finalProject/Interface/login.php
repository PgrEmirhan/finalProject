<?php
// login.php
session_start();
if (isset($_SESSION['message'])) {
    echo "<p>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
}
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Formdan gelen verileri al
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Kullanıcıyı veritabanından al
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Başarılı giriş
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $user['id'];

        header("Location: upload.php"); // Yönlendirme dosya yükleme sayfasına
        exit();
    } else {
        echo "Hatalı kullanıcı adı veya şifre!";
    }
}
?>

<!-- Giriş formu -->
<form action="login.php" method="POST">
    <label for="username">Kullanıcı Adı:</label>
    <input type="text" name="username" required><br><br>
    <label for="password">Şifre:</label>
    <input type="password" name="password" required><br><br>
    <button type="submit">Giriş Yap</button>
</form>
