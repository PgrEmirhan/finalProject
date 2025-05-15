<?php
    $host = 'localhost'; //Veri tabanı sunucusu yerel makineden çalışıyor
    $dbname = 'file_sharing'; //Bağlanılan veri tabanı
    $username = 'root'; // Veri tabanı Kullanıcı adı
    $password = ''; // Veri tabanı parolası

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
    }
?>