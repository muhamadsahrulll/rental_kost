<?php

// Autoload Midtrans
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = $_ENV['MIDTRANS_SERVER_KEY'];
\Midtrans\Config::$isProduction = $_ENV['MIDTRANS_IS_PRODUCTION'];
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
