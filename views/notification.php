<?php
require_once '../config.php';
require_once '../vendor/autoload.php';

$notif = new \Midtrans\Notification();
$status = $notif->transaction_status;
$order_id = $notif->order_id;

$trx = $conn->query("SELECT * FROM transactions WHERE order_id = '$order_id'")->fetch_assoc();

if ($status == 'settlement') {
    $conn->query("UPDATE transactions SET status = 'success' WHERE order_id = '$order_id'");
    $conn->query("UPDATE kost SET tersedia = tersedia - 1 WHERE id = {$trx['kost_id']}");
} elseif ($status == 'cancel' || $status == 'expire') {
    $conn->query("UPDATE transactions SET status = 'cancel' WHERE order_id = '$order_id'");
    $conn->query("UPDATE kost SET tersedia = tersedia + 1 WHERE id = {$trx['kost_id']}");
}
