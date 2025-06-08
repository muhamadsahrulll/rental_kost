<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validasi login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

$user_id = $_SESSION['user_id'];
$kost_id = (int)($data['kost_id'] ?? 0);
$order_id = $data['order_id'] ?? '';
$amount = (int)($data['amount'] ?? 0);
$status = $data['status'] ?? '';
$snap_token = $data['snap_token'] ?? '';
$payment_type = $data['payment_type'] ?? '';

// Validasi data
if (!$kost_id || !$order_id || !$amount || !$snap_token || !$status) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

$conn->autocommit(false);

try {
    // Cek apakah transaksi dengan order_id sudah ada
    $check = $conn->prepare("SELECT id FROM transactions WHERE order_id = ?");
    $check->bind_param("s", $order_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Jika sudah ada → update status & detail
        $update_settlement_time = ($status === 'settlement') ? ", settlement_time = NOW()" : "";

        $stmt = $conn->prepare("UPDATE transactions 
            SET status = ?, amount = ?, snap_token = ?, payment_type = ? $update_settlement_time 
            WHERE order_id = ?");
        $stmt->bind_param("sdsss", $status, $amount, $snap_token, $payment_type, $order_id);
        $stmt->execute();
    } else {
        // Kalau belum ada → kurangi stok + insert transaksi baru
        $stmt = $conn->prepare("UPDATE kost SET kamar_tersisa = kamar_tersisa - 1 WHERE id = ? AND kamar_tersisa > 0");
        $stmt->bind_param("i", $kost_id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception('Kamar sudah habis');
        }

        // Set waktu settlement jika statusnya settlement
$settlement_time = null;
if ($status === 'settlement') {
    $settlement_time = date('Y-m-d H:i:s');
}

$stmt = $conn->prepare("INSERT INTO transactions 
    (user_id, kost_id, order_id, amount, status, snap_token, payment_type, settlement_time) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("iisdssss", $user_id, $kost_id, $order_id, $amount, $status, $snap_token, $payment_type, $settlement_time);
        $stmt->execute();
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Transaksi berhasil disimpan'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
