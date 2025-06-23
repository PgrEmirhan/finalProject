<?php
require_once(__DIR__ . '/../vendor/autoload.php');

$clientID = '615044280483-sgrn39pqfep6v15fu484f6o8l45fki9p.apps.googleusercontent.com'; // kendi client ID'n ile değiştir
$clientSecret = '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'; // kendi secret ile değiştir
$redirectUri = 'http://localhost/finalProject/frontend/google-callback.php'; // kendi URL'n

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");
?>
