<?php
session_start();
header('Content-Type: application/json');
require_once './includes/db.php'; // koneksi database

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada data JSON.']);
    exit;
}

if (empty($data['order_id']) || empty($data['transaction_status']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$user_id       = $_SESSION['user_id'];
$kost_id       = $_SESSION['last_kost_id'] ?? null;
$order_id      = $conn->real_escape_string($data['order_id']);
$amount        = floatval($data['gross_amount']);
$status        = $conn->real_escape_string($data['transaction_status']);
$payment_type  = $conn->real_escape_string($data['payment_type']);
$created_at    = date('Y-m-d H:i:s');

if (!$kost_id) {
    echo json_encode(['success' => false, 'message' => 'Kost ID tidak ditemukan']);
    exit;
}

// Simpan transaksi
$stmt = $conn->prepare("INSERT INTO transactions (user_id, kost_id, order_id, amount, status, payment_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisdsss", $user_id, $kost_id, $order_id, $amount, $status, $payment_type, $created_at);

if ($stmt->execute()) {
    if ($status == 'pending') {
        $conn->query("UPDATE kost SET kamar_tersisa = kamar_tersisa - 1 WHERE id = $kost_id");
    }

    // Insert notifikasi jika sukses bayar
    if ($status === 'settlement') {
        $msg = "Pembayaran untuk kos ID $kost_id telah berhasil.";
        $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
        $stmtNotif->bind_param("is", $user_id, $msg);
        $stmtNotif->execute();
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal simpan DB']);
}
