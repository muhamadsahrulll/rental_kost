<?php
session_start();
require_once '../../includes/db.php';

header('Content-Type: application/json');

// Cek input dan login user
if (!isset($_POST['transaction_id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid']);
    exit;
}

$transaction_id = intval($_POST['transaction_id']);
$user_id = $_SESSION['user_id'];

// Ambil data transaksi
$stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $transaction_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan atau bukan milik Anda.']);
    exit;
}

$transaction = $result->fetch_assoc();

// Pastikan status transaksi masih pending (belum dibayar)
if ($transaction['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Transaksi hanya bisa dibatalkan jika statusnya pending.']);
    exit;
}

// Update status transaksi menjadi cancelled
$upd = $conn->prepare("UPDATE transactions SET status = 'cancelled', snap_token = NULL WHERE id = ?");
$upd->bind_param("i", $transaction_id);
if (!$upd->execute()) {
    echo json_encode(['success' => false, 'message' => 'Gagal membatalkan transaksi.']);
    exit;
}

// Kembalikan stok kamar di kost
$kost_id = $transaction['kost_id'];
$conn->query("UPDATE kost SET kamar_tersisa = kamar_tersisa + 1 WHERE id = $kost_id");

echo json_encode(['success' => true, 'message' => 'Transaksi berhasil dibatalkan.']);
