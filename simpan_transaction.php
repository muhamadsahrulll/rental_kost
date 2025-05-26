<?php
session_start();
header('Content-Type: application/json');
require_once './includes/db.php'; // koneksi database

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['order_id']) || empty($data['transaction_status']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$user_id       = $_SESSION['user_id'];
$kost_id       = $_SESSION['last_kost_id'] ?? null; // Simpan dari session sebelumnya
$order_id      = $data['order_id'];
$amount        = $data['gross_amount'];
$status        = $data['transaction_status'];
$payment_type  = $data['payment_type'];
$created_at    = date('Y-m-d H:i:s');

if (!$kost_id) {
    echo json_encode(['success' => false, 'message' => 'Kost ID tidak ditemukan']);
    exit;
}

// Simpan transaksi ke database
$stmt = $conn->prepare("INSERT INTO transactions (user_id, kost_id, order_id, amount, status, payment_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisdsss", $user_id, $kost_id, $order_id, $amount, $status, $payment_type, $created_at);

if ($stmt->execute()) {
    // Update ketersediaan kamar jika pending
    if ($status == 'pending') {
        $conn->query("UPDATE kost SET kamar_tersisa = kamar_tersisa - 1 WHERE id = $kost_id");
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal simpan DB']);
}
