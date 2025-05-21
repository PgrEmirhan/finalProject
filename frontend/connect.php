<?php 
$host = 'localhost';
$dbname = 'file_sharing';
$username = 'root';
$password = '';
try{
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Şu anda sistemsel bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.");} 
?>