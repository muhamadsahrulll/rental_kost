<?php
require_once '../../includes/db.php';
header('Content-Type: application/json');
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$action = $data['action'] ?? '';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pemilik') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

if (!$id || !in_array($action, ['accept', 'reject'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
    exit;
}

if ($action === 'accept') {
    $stmt = $conn->prepare("UPDATE transactions SET is_accepted = 'accepted' WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    // Tolak → Refund otomatis
    $stmt = $conn->prepare("UPDATE transactions SET status = 'refund', is_accepted = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $id);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Permintaan berhasil diproses']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal memproses data']);
}
