<?php

use Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$url = $_ENV['WEB_URL'];

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'None');
ini_set('session.use_strict_mode', 1);
session_start();

header("Access-Control-Allow-Origin: $url");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');
