<?php
    require_once ROOT_PATH . '/Parabook/Web/vendor/autoload.php';
    use Dotenv\Dotenv;
    $dotenv = Dotenv::createImmutable(ROOT_PATH . '/Parabook/Web/php');
    $dotenv->load();

    $gClient = new Google_Client();
    $gClient->setClientId($_ENV['gClientId']);
    $gClient->setClientSecret($_ENV['gClientSecret']);
    $gClient->setApplicationName("PHP");
    $gClient->setRedirectUri($_ENV['gRedirect']);
    $gClient->addScope("email");
    $gClient->addScope("profile");

    $gClient->setPrompt('select_account');

    $login_url = $gClient->createAuthUrl();

?>