<?php
require_once 'env.php';
$serviceToken = $TOKEN;
$project = $PROJECT;
$config = $CONFIG;

$url = "https://api.doppler.com/v3/configs/config/secrets/download?project={$project}&config={$config}&format=json";


$headers = [
    "Authorization: Bearer {$serviceToken}"
];

$options = [
    'http' => [
        'method' => 'GET',
        'header' => implode("\r\n", $headers),
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    $error = error_get_last();
    die("Failed to fetch secrets from Doppler: " . $error['message']);
}

$secrets = json_decode($response, true);
$g_client_id = $secrets['G_CLIENT_ID'];
$g_client_secret = $secrets['G_CLIENT_SECRET'];
$g_redirect_url = $secrets['G_REDIRECT_URL'];
$f_app_id = $secrets['F_APP_ID'];
$f_app_secret = $secrets['F_APP_SECRET'];
$f_redirect_url = $secrets['F_REDIRECT_URL'];

?>