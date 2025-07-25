<?php

    $basePath = '/ParaBook';
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (strpos($uri, $basePath) === 0) {
        $uri = substr($uri, strlen($basePath));
    }

    $uri = rtrim($uri, '/') ?: '/';

    const LOG_SIGN =  'Web/php/login_signup';
    const PHP = 'Web/php/';

    $routes = [
        
        //admin
        '/adminhome' => PHP . 'ADMIN/home.php',
        '/adminlogin' => PHP . 'ADMIN/login.php',
        '/adminlogout' => PHP . 'ADMIN/logout.php',
        '/adminsignup' => PHP . 'ADMIN/signup.php',
        '/createadmins' => PHP . 'ADMIN/createAdmins.php',
        '/logincheck' => PHP . 'ADMIN/logincheck.php',
        '/servicedetails' => PHP . 'ADMIN/serviceDetails.php',
        
        // login and signup
        '/' => LOG_SIGN . '/login.php',
        '/login' => LOG_SIGN . '/login.php',
        '/logincheck' => LOG_SIGN . '/loginCheck.php',
        '/signup' => LOG_SIGN . '/signup.php',
        '/signcheck' => LOG_SIGN . '/signCheck.php',
        '/verify' => LOG_SIGN . '/verify.php',
        '/logout' => LOG_SIGN . '/logout.php',
        
        // home, google and facebook
        '/home' => PHP . 'views/home.php',
        '/auth/google' => PHP . 'google/auth_provider.php',
        '/google/callback' => PHP . 'google/controller.php',
        '/facebook/callback' => PHP . 'facebook/f-callback.php',
        
        // service and account selection
        '/servicePost' => PHP . 'formDatabase/serviceForm.data.php',
        '/accountSelection' => PHP . 'views/accountSelection.php',
        '/serviceDescription' => PHP . 'views/serviceDesc.php',
        
        // for booking
        '/bookingpassenger' => PHP . 'views/bookingForm_P.php',
        '/bookingpassengerUpdate' => PHP . 'views/bookingFormUpdate_P.php',
        '/bookingcompany' => PHP . 'views/booking_C.php',
        '/bookingSubmit' => PHP. 'AJAX/bookingSubmit.php',
        '/bookingcheck' => PHP . 'views/bookingCheck_P.php',
        
        // for statistics
        '/statistics' => PHP . 'views/statistics.php',
        '/statisticsPassenger' => PHP . 'views/statistics_P.php',
        '/statisticsCompany' => PHP . 'views/statistics_C.php',
        
        '/completeProfile' => PHP . 'views/complete.profile.php',
        
        // eSewa
        '/eSewaPayment' => PHP . 'eSewa/eSewaPayment.php',
        '/booking-success' => PHP . 'eSewa/pageSuccess.php',
        '/booking-error' => PHP . 'eSewa/pageError.php',
        
        // profile
        '/profile' => PHP . 'views/profile.php',

        // settings
        '/settings' => PHP . 'views/settings_menu.php',
        
        //chat system
        '/chat' => PHP . 'views/chat.php',
        
    ];

    function routeToController($uri, $routes) {
        try {
            $uri = $uri === '' ? '/' : $uri;

            if (array_key_exists($uri, $routes)) {
                $controllerPath = $routes[$uri];
                
                $fullPath = $controllerPath;
                if (file_exists($fullPath)) {                
                    require_once $fullPath;
                } else {
                    throw new Exception("Controller file not found: " . $fullPath);
                }
            } else {
                abort();
            }
        } catch (Exception $e) {
            abort(500, $e->getMessage());
        }
    }

    function abort($code = 404, $message = '') {
        http_response_code($code);
        
        // Error pages
        $errorPages = [
            404 => 'Web/php/Error/404.html',
            500 => 'Web/php/Error/403.html',
        ];
        
        if (isset($errorPages[$code]) && file_exists($errorPages[$code])) {
            require $errorPages[$code];
        } else {
            
            $errorMessages = [
                404 => 'Page Not Found',
                500 => 'Internal Server Error: ' . $message,
            ];
            die("<h1>{$code} - {$errorMessages[$code]}</h1>");
        }
        die();
    }

    routeToController($uri, $routes);
?>