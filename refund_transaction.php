<?php
session_start();
header('Content-Type: application/json');
require './includes/db.php';

if (!isset($_SESSION['user_id']) || empty($_POST['transaction_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses tidak valid']);
    exit;
}

$user_id = $_SESSION['user_id'];
$transaction_id = intval($_POST['transaction_id']);

// Cek apakah transaksi valid dan milik user
$stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $transaction_id, $user_id);
$stmt->execute();
$trans = $stmt->get_result()->fetch_assoc();

if (!$trans || $trans['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Transaksi tidak valid atau tidak bisa direfund']);
    exit;
}

$amount = floatval($trans['amount']);
$kost_id = $trans['kost_id'];

// Update status transaksi menjadi refund
$update = $conn->prepare("UPDATE transactions SET status = 'refund', updated_at = NOW() WHERE id = ?");
$update->bind_param("i", $transaction_id);
$update->execute();

// Tambahkan kamar kembali ke kost
$conn->query("UPDATE kost SET kamar_tersisa = kamar_tersisa + 1 WHERE id = $kost_id");

// Pastikan wallet user ada
$conn->query("INSERT IGNORE INTO wallets (user_id, balance) VALUES ($user_id, 0)");

// Ambil ID wallet
$walletResult = $conn->query("SELECT id FROM wallets WHERE user_id = $user_id");
$wallet = $walletResult->fetch_assoc();
$wallet_id = $wallet['id'];

// Tambahkan saldo ke wallet
$conn->query("UPDATE wallets SET balance = balance + $amount WHERE user_id = $user_id");

// Simpan histori transaksi wallet
$stmt = $conn->prepare("INSERT INTO wallet_transactions (wallet_id, amount, type, description, created_at) VALUES (?, ?, 'refund', ?, NOW())");
$desc = "Refund transaksi kost ID $kost_id";
$stmt->bind_param("ids", $wallet_id, $amount, $desc);
$stmt->execute();

echo json_encode(['success' => true, 'message' => 'Refund berhasil dan dana masuk ke wallet']);
