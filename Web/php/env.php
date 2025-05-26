<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
// Access environment variables
$TOKEN = $_ENV['serviceToken'];
$PROJECT = $_ENV['project'];
$CONFIG = $_ENV['config'];
$MAIL = $_ENV['mail'];
$PASSWORD = $_ENV['Password'];



?>