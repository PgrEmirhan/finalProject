    <?php
    session_start();

    // Misafir kullanıcı olarak giriş yapma
    if (isset($_GET['guest']) && $_GET['guest'] == 'true') {
        // Misafir kullanıcıya özel bir oturum başlatıyoruz
        $_SESSION['guest'] = true;
        // Misafir kullanıcı olarak ana sayfaya yönlendirme yapıyoruz
        header("Location: guest_upload.php");
        exit(); // Yönlendirme sonrası işlemin durmasını sağla
    }

    // Kullanıcı giriş yapmış mı kontrol et
    if (isset($_SESSION['user_id'])) {
        // Girişli kullanıcı işlemleri burada olacak
    } elseif (isset($_SESSION['guest']) && $_SESSION['guest'] == true) {
        // Misafir kullanıcı işlemleri burada olacak
        // Misafir kullanıcı, giriş yapmadan dosya yükleyebilir vb.
    } else {
        // Giriş yapmayan kullanıcılar için işlemler
        // (Misafir olmayan)
    }

    ?>

    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ana Sayfa - Dosya Paylaşım</title>
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
            }

            .container {
                width: 100%;
                max-width: 1200px;
                padding: 20px;
                text-align: center;
            }

            .header {
                margin-bottom: 30px;
                text-align: center;
            }

            h1 {
                font-size: 48px;
                color: #4CAF50;
                margin: 0;
                transition: all 0.3s ease;
            }

            h1:hover {
                color: #45a049;
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

            .btn:active {
                transform: scale(0.98);
            }

            /* Responsive tasarım */
            @media (max-width: 768px) {
                h1 {
                    font-size: 36px;
                }

                p {
                    font-size: 16px;
                }

                .button-container {
                    flex-direction: column;
                }

                .btn {
                    width: 100%;
                    padding: 15px;
                    font-size: 16px;
                }
            }

            /* Misafir olarak dene butonu */
            .guest-btn-container {
                margin-top: 20px; /* Giriş yap ve kayıt ol butonlarından biraz mesafe */
            }

            .btn-guest {
                background-color: #FF5722; /* Misafir butonu için farklı bir renk */
            }

            .btn-guest:hover {
                background-color: #E64A19;
            }

            /* Misafir olarak giriş yapıldığı mesaj */
            .guest-message {
                color: green;
                font-size: 20px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
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
    </body>
    </html>
