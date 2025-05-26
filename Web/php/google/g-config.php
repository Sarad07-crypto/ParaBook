<?php
require_once '../../vendor/autoload.php';
require_once '../api-config.php';

$gClient = new Google_Client();
$gClient->setClientId($g_client_id);
$gClient->setClientSecret($g_client_secret);
$gClient->setApplicationName("PHP");
$gClient->setRedirectUri($g_redirect_url);
$gClient->addScope("email");
$gClient->addScope("profile");

$gClient->setPrompt('select_account');

$login_url = $gClient->createAuthUrl();

?>