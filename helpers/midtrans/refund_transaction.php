<?php
session_start();
require_once '../../includes/db.php';
require_once '../../vendor/autoload.php';
require_once '../../config.php';

header('Content-Type: application/json');

// Validasi input & sesi
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
$kost_id = $transaction['kost_id'];

// Hanya transaksi settlement yang bisa di-refund
if ($transaction['status'] !== 'settlement') {
    echo json_encode(['success' => false, 'message' => 'Transaksi belum dibayar atau sudah dibatalkan.']);
    exit;
}

// Coba refund via Midtrans
try {
    $refundParams = [
        'refund_key' => 'refund-' . time(),
        'amount' => $transaction['amount'],
        'reason' => 'User cancel transaction'
    ];

    $response = \Midtrans\Transaction::refund($transaction['order_id'], $refundParams);

    // Jika sukses refund, update status
    $upd = $conn->prepare("UPDATE transactions SET status = 'refund', snap_token = NULL WHERE id = ?");
    $upd->bind_param("i", $transaction_id);
    $upd->execute();

    // Kembalikan stok kamar
    $conn->query("UPDATE kost SET kamar_tersisa = kamar_tersisa + 1 WHERE id = $kost_id");

    echo json_encode(['success' => true, 'message' => 'Refund berhasil diproses.']);
} catch (Exception $e) {
    $errorMsg = $e->getMessage();

    // Jika error karena provider menolak refund otomatis
    if (strpos($errorMsg, 'Payment Provider') !== false) {
        // Simpan sebagai refund manual
        $stmt = $conn->prepare("UPDATE transactions SET status = 'refund_manual', snap_token = NULL WHERE id = ?");
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();

        // Tambah stok kamar
        $conn->query("UPDATE kost SET kamar_tersisa = kamar_tersisa + 1 WHERE id = $kost_id");

        // (Opsional) Simpan ke tabel refund_requests
        $reason = "Gagal refund otomatis: $errorMsg";
        $stmt = $conn->prepare("INSERT INTO refund_requests (user_id, transaction_id, reason, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $transaction_id, $reason);
        $stmt->execute();

        echo json_encode([
            'success' => false,
            'message' => 'Refund otomatis gagal (batas waktu tercapai). Admin akan memproses refund manual.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal refund: ' . $errorMsg]);
    }
}
