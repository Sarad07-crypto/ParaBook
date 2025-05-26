<?php
    session_start();
    require_once("../../vendor/Facebook/autoload.php");
    require_once '../api-config.php';

    $fbObject = new \Facebook\Facebook([
        'app_id' => $f_app_id,
        'app_secret' => $f_app_secret,
        'default_graph_version' => 'v18.0'
    ]);

    $handler = $fbObject -> getRedirectLoginHelper();

    $redirectTo = $f_redirect_url; 
    $data = ['email'];
    $loginParams = [
        'auth_type' => 'select_account'
    ];
    $fullURL = $handler->getLoginUrl($redirectTo, $data);

?>