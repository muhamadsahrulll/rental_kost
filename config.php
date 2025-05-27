<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if(empty($_ENV['MIDTRANS_SERVER_KEY'])) {
    die("MIDTRANS_SERVER_KEY belum di-set di .env");
}

\Midtrans\Config::$serverKey = $_ENV['MIDTRANS_SERVER_KEY'];
\Midtrans\Config::$isProduction = filter_var($_ENV['MIDTRANS_IS_PRODUCTION'], FILTER_VALIDATE_BOOLEAN);
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
