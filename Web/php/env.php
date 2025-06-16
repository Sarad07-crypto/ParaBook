<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
// Access environment variables
$TOKEN = $_ENV['serviceToken'];

$G_CLIENT_ID = $_ENV['gClientId'];
$G_CLIENT_SECRET = $_ENV['gClientSecret'];
$G_REDIRECT = $_ENV['gRedirect'];

$F_APP_ID = $_ENV['fAppId'];
$F_APP_SECRET = $_ENV['fAppSecret'];
$F_REDIRECT = $_ENV['fRedirect'];

$PROJECT = $_ENV['project'];
$CONFIG = $_ENV['config'];
$MAIL = $_ENV['mail'];
$PASSWORD = $_ENV['Password'];

?>