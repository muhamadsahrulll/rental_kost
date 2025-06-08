<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if(empty($_ENV['MIDTRANS_SERVER_KEY'])) {
    die("MIDTRANS_SERVER_KEY belum di-set di .env");
}

\Midtrans\Config::$serverKey = "SB-Mid-server-ddxZGQZoHixMDraiMgHSWhhp";
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
