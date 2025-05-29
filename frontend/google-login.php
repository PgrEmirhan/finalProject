<?php
require_once 'config.php';

$authUrl = $client->createAuthUrl();

// Kullanıcıyı Google giriş sayfasına yönlendir
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;
?>