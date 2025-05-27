<?php
session_start();
header('Content-Type: application/json');

require_once './includes/db.php';
require_once './vendor/autoload.php';
require_once './config.php';

if (!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Data tidak lengkap']);
    exit;
}

$old_order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Ambil transaksi yang pending
$stmt = $conn->prepare("SELECT t.*, k.nama_kost FROM transactions t JOIN kost k ON t.kost_id = k.id WHERE t.order_id = ? AND t.user_id = ? AND t.status = 'pending'");
$stmt->bind_param("si", $old_order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$trans = $result->fetch_assoc();

if (!$trans) {
    echo json_encode(['error' => 'Transaksi tidak ditemukan atau tidak valid']);
    exit;
}

// Buat order_id baru
$new_order_id = 'ORDER-' . time();
$amount = (int)$trans['amount'];

// Update order_id di database
$update = $conn->prepare("UPDATE transactions SET order_id = ? WHERE id = ?");
$update->bind_param("si", $new_order_id, $trans['id']);
$update->execute();

// Buat Snap Token baru
$params = [
    'transaction_details' => [
        'order_id' => $new_order_id,
        'gross_amount' => $amount
    ],
    'item_details' => [[
        'id' => $trans['kost_id'],
        'price' => $amount,
        'quantity' => 1,
        'name' => $trans['nama_kost']
    ]],
    'customer_details' => [
        'first_name' => $_SESSION['username'] ?? 'User',
        'email' => 'example@email.com'
    ]
];

try {
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    echo json_encode(['token' => $snapToken]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
