<?php
require_once 'config.php';

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $userData = $google_oauth->userinfo->get();

        echo "Hoşgeldiniz, " . $userData->name . "<br>";
        echo "Email: " . $userData->email . "<br>";
        echo "<img src='" . $userData->picture . "'>";
    } else {
        echo "Hata oluştu: " . $token['error_description'];
    }
} else {
    echo "Yetkilendirme kodu alınamadı.";
}
?>