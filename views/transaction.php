<?php
session_start();
require_once '../includes/db.php';
require_once '../vendor/autoload.php';
require_once '../config.php';

header('Content-Type: application/json');

// Cek login dan POST
if (!isset($_SESSION['user_id']) || !isset($_POST['kost_id'])) {
    echo json_encode(['error' => 'Data tidak lengkap.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$kost_id = intval($_POST['kost_id']);
$_SESSION['last_kost_id'] = $kost_id;

// Ambil data kos
$result = $conn->query("SELECT * FROM kost WHERE id = $kost_id");
if (!$result || $result->num_rows === 0) {
    echo json_encode(['error' => 'Kost tidak ditemukan.']);
    exit;
}
$kost = $result->fetch_assoc();

// Snap Token
$transaction_details = [
    'order_id' => 'ORDER-' . time(),
    'gross_amount' => (int)$kost['harga_per_bulan']
];
$_SESSION['order_id'] = $transaction_details['order_id'];

$params = [
    'transaction_details' => $transaction_details,
    'item_details' => [[
        'id' => $kost_id,
        'price' => (int)$kost['harga_per_bulan'],
        'quantity' => 1,
        'name' => $kost['nama_kost']
    ]],
    'customer_details' => [
        'first_name' => $_SESSION['username'],
        'email' => 'example@email.com'
    ]
];

try {
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    echo json_encode(['token' => $snapToken]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
