<?php

    session_start();
    require_once __DIR__ . '/../../../index.php';
    require_once __DIR__ . '/core/controller.class.php';
    require_once __DIR__ . '/g-config.php';

    if (!isset($_SESSION['oAuth_provider'])) {
        header('Location: /');
        exit();
    }
    $provider = $_SESSION['oAuth_provider'];
    
    if ($provider === 'google' && isset($_GET['code'])) {
        
        $token = $gClient->fetchAccessTokenWithAuthCode($_GET["code"]);
        

            if (!isset($token['error']) || $token['error'] !== "invalid_grant") {
                $oAuth = new Google_Service_Oauth2($gClient);
                $userData = $oAuth->userinfo_v2_me->get();

                $_SESSION['user_email'] = $userData['email'];
                $_SESSION['givenName'] = $userData['givenName'];
                $_SESSION['familyName'] = $userData['familyName'];
                $_SESSION['avatar'] = $userData['picture'];
                $_SESSION['g_id'] = $userData['id'];
                $_SESSION['sign_with'] = 'google';
                
                $Controller = new Controller;
                echo $Controller->insertData(array(
                    'email' => $userData['email'],
                    'avatar' => $userData['picture'],
                    'givenName' => $userData['givenName'],
                    'familyName' => $userData['familyName'],
                    'g_id' => $userData['id'],
                    'sign_with' => 'google'
                ));
            } else {
                echo "Error while getting user data.";
            }
    }  else {
        die("Invalid provider");
        header('Location: /');
        exit();
    }

?>