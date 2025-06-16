<?php
    session_start();
    require_once ROOT_PATH . '\Parabook\Web\vendor\Facebook\autoload.php';
    require_once ROOT_PATH . '/Parabook/Web/vendor/autoload.php';
    
    use Dotenv\Dotenv;
    $dotenv = Dotenv::createImmutable(ROOT_PATH . '/Parabook/Web/php');
    $dotenv->load();
    
    $fbObject = new \Facebook\Facebook([
        'app_id' => $_ENV['fAppId'],
        'app_secret' => $_ENV['fAppSecret'],
        'default_graph_version' => 'v18.0'
    ]);

    $handler = $fbObject -> getRedirectLoginHelper();

    $redirectTo = $_ENV['fRedirect'];
    $data = ['email'];
    $loginParams = [
        'auth_type' => 'select_account'
    ];
    $fullURL = $handler->getLoginUrl($redirectTo, $data);

?>