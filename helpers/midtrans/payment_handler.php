<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db.php';
require_once '../../config.php';
require_once '../../vendor/autoload.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'Akses ditolak']));
}

$kost_id = isset($_POST['kost_id']) ? (int)$_POST['kost_id'] : 0;
if ($kost_id <= 0) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'ID Kost tidak valid']));
}

$kost = getKostById($kost_id);
if (!$kost) {
    http_response_code(404);
    die(json_encode(['status' => 'error', 'message' => 'Kost tidak ditemukan']));
}

$order_id = 'KOST-' . time() . '-' . $_SESSION['user_id'] . '-' . $kost_id;

$transaction_data = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => $kost['harga_per_bulan']
    ],
    'item_details' => [[
        'id' => $kost_id,
        'price' => $kost['harga_per_bulan'],
        'quantity' => 1,
        'name' => 'Sewa Kost ' . $kost['nama_kost']
    ]],
    'customer_details' => [
        'username' => $_SESSION['username'],
        // 'email' => $_SESSION['email'],
    ]
];

try {
    $snapToken = \Midtrans\Snap::getSnapToken($transaction_data);
    echo json_encode([
        'status' => 'success',
        'snap_token' => $snapToken,
        'order_id' => $order_id,
        'amount' => $kost['harga_per_bulan']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
