<?php
session_start();

// Misafir kullanıcı olarak giriş yapma 
if (isset($_GET['guest']) && $_GET['guest'] == 'true') {
    $_SESSION['guest'] = true;
    header("Location: guest_upload.php");
    exit();
}

// Kullanıcı giriş yapmış mı kontrol et
if (isset($_SESSION['user_id'])) {
    // Girişli kullanıcı işlemleri burada olacak
} elseif (isset($_SESSION['guest']) && $_SESSION['guest'] == true) {
    // Misafir kullanıcı işlemleri burada olacak
} else {
    // Giriş yapmayan kullanıcılar için işlemler
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ana Sayfa - Dosya Paylaşım</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    <style>
        /* Genel stil ayarları */
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
            overflow: hidden;
            font-weight: 550;  
        }
        h1{
            font-family: 'Ubuntu', sans-serif;
            font-size: 48px;
        }
        /* Preloader (Spinner) */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;
        }

        .spinner {
            border: 10px solid rgb(0, 0, 0); /* Daha kalın bir çerçeve */
            border-top: 10px dashed rgb(0, 0, 0); /* Daha belirgin bir renk */
            border-radius: 30%;
            width: 100px; /* Büyütüldü */
            height: 100px; /* Büyütüldü */
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Sayfa içeriği */
        .container {
            display: none;
            text-align: center;
            padding: 50px;
        }

        /* Yükleme animasyonu sonrasında içerik görünür olacak */
        body.loaded .preloader {
            display: none;
        }

        body.loaded .container {
            display: block;
        }

        h1 {
            font-size: 64px;
            color: #4CAF50;
            margin: 0;
        }

        p {
            font-size: 18px;
            color: #666;
        }

        /* Butonlar */
        .button-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            font-size: 18px;
            padding: 15px 30px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }

        /* Misafir olarak dene butonu */
        .guest-btn-container {
            margin-top: 20px;
        }

        .btn-guest {
            background-color: #FF5722; 
        }

        .btn-guest:hover {
            background-color: #E64A19; 
        }

        
        .btn-guest:hover {
            
        }

        .guest-message {
            color: green;
            font-size: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Preloader Div -->
    <div class="preloader">
        <div class="spinner"> 
    </div>
    </div>

    <div class="container">
        <div class="header">
            <h1>Dosya Paylaşım Sistemi</h1>
            <p>Güvenli ve hızlı bir şekilde dosyalarınızı paylaşın ve yönetin.</p>
        </div>

        <?php if (isset($_GET['guest_logged_in']) && $_GET['guest_logged_in'] == 'true'): ?>
            <p class="guest-message">Misafir olarak giriş yaptınız. Artık dosya yükleyebilir ve paylaşabilirsiniz!</p>
        <?php endif; ?>

        <div class="button-container">
            <a href="login.php" class="btn">Giriş Yap</a>
            <a href="register.php" class="btn">Kayıt Ol</a>
        </div>

        <div class="button-container guest-btn-container">
            <a href="index.php?guest=true" class="btn btn-guest">Misafir Olarak Dene</a>
        </div>
    </div>

    <script>
        window.addEventListener("load", function () {
            // 2 saniye gecikme ekleyerek, preloader'ın kapanmasını bekliyoruz
            setTimeout(function() {
                document.body.classList.add("loaded");
            }, 5000); // 2 saniye gecikme
        });
    </script>
</body>
</html>
