<?php

$basePath = '/ParaBook'; // Set this to your subdirectory
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

$uri = rtrim($uri, '/') ?: '/';

const log_sign =  'Web/php/login_signup';

$routes = [

    '/' => log_sign . '/login.php',
    '/login' => log_sign . '/login.php',
    '/logincheck' => log_sign . '/loginCheck.php',
    '/signup' => log_sign . '/signup.php',
    '/signcheck' => log_sign . '/signCheck.php',
    '/verify' => log_sign . '/verify.php',
    '/logout' => log_sign . '/logout.php',
    
    '/home' => 'Web/php/views/home.php',
    '/auth/google' => 'Web/php/google/auth_provider.php',
    '/google/callback' => 'Web/php/google/controller.php',
    '/facebook/callback' => 'Web/php/facebook/f-callback.php',
    
];

function routeToController($uri, $routes) {
    try {
        $uri = $uri === '' ? '/' : $uri;

        if (array_key_exists($uri, $routes)) {
            $controllerPath = $routes[$uri];
            
            $fullPath = $controllerPath;
            if (file_exists($fullPath)) {                
                require_once $fullPath; // Use require_once to avoid double-inclusion
            } else {
                throw new Exception("Controller file not found: " . $fullPath);
            }
        } else {
            abort(); // Define a custom abort() for 404 or redirect
        }
    } catch (Exception $e) {
        abort(500, $e->getMessage()); // You can show a custom error page
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