    <?php
    // register.php
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

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Formdan gelen verileri al
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);  // Şifreyi hashle

        // Veritabanına kullanıcıyı ekle
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);

    // Kayıt işleminden sonra
    if ($registration_success) {
        $_SESSION['message'] = 'Kayıt başarılı! Lütfen giriş yapın.';
        header("Location: login.php");
        exit();
    }

    }
    ?>

    <!-- Kayıt formu -->
    <form action="register.php" method="POST">
        <label for="username">Kullanıcı Adı:</label>
        <input type="text" name="username" required><br><br>
        <label for="password">Şifre:</label>
        <input type="password" name="password" required><br><br>
        <button type="submit">Kayıt Ol</button>
    </form>
