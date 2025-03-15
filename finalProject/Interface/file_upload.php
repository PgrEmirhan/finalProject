<?php
// file_upload.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dosya Yükle</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f9fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            animation: backgroundFade 1s ease-out;
        }

        .form-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            transform: scale(0.5);
            animation: formFadeIn 1s ease-out forwards;
        }

        h2 {
            text-align: center;
            color: #4CAF50;
            font-size: 36px;
            margin-bottom: 30px;
            animation: fadeIn 2s ease-out;
        }

        .form-container input {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 2px solid #ddd;
            font-size: 16px;
            opacity: 0;
            transform: translateY(100px);
            animation: slideInUp 0.6s ease-out forwards;
        }

        .form-container button {
            width: 100%;
            padding: 15px;
            background-color: #4CAF50;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-container button:hover {
            background-color: #45a049;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes formFadeIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes slideInUp {
            0% { opacity: 0; transform: translateY(100px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes backgroundFade {
            0% { background-color: #f7f9fc; }
            100% { background-color: #e1f5e4; }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Dosya Yükle</h2>
        <form action="file_upload.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit" name="submit">Dosya Yükle</button>
        </form>
    </div>
</body>
</html>
