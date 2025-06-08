<?php
// session_start();
// require_once '../includes/db.php';
// require_once '../vendor/autoload.php';
// require_once '../config.php';

// header('Content-Type: application/json');

// // Validasi input
// if (!isset($_SESSION['user_id']) || !isset($_POST['kost_id'])) {
//     echo json_encode(['error' => 'Data tidak lengkap.']);
//     exit;
// }

// $user_id = $_SESSION['user_id'];
// $kost_id = intval($_POST['kost_id']);
// $_SESSION['last_kost_id'] = $kost_id; // simpan untuk proses selanjutnya

// // Handle aksi update stok
// if ($_POST['action'] === 'decrease') {
//     $stmt = $conn->prepare("UPDATE kost SET kamar_tersisa = kamar_tersisa - 1 WHERE id = ? AND kamar_tersisa > 0");
//     $stmt->bind_param("i", $kost_id);
//     $stmt->execute();
//     echo json_encode(['success' => $stmt->affected_rows > 0]);
//     exit;
// }

// // Ambil data kost
// $result = $conn->query("SELECT * FROM kost WHERE id = $kost_id");
// if (!$result || $result->num_rows === 0) {
//     echo json_encode(['error' => 'Kost tidak ditemukan.']);
//     exit;
// }

// $kost = $result->fetch_assoc();
// $harga = (int)$kost['harga_per_bulan'];
// $nama_kost = $kost['nama_kost'];
// $order_id = 'ORDER-' . uniqid();

// // Siapkan parameter Snap
// $params = [
//     'transaction_details' => [
//         'order_id' => $order_id,
//         'gross_amount' => $harga
//     ],
//     'item_details' => [[
//         'id' => $kost_id,
//         'price' => $harga,
//         'quantity' => 1,
//         'name' => $nama_kost
//     ]],
//     'customer_details' => [
//         'first_name' => $_SESSION['username'] ?? 'User',
//         'email' => $_SESSION['email'] ?? 'customer@example.com'
//     ]
// ];

// try {
//     $snapToken = \Midtrans\Snap::getSnapToken($params);

//     // Simpan Snap token (opsional)
//     $stmt = $conn->prepare("INSERT INTO transactions (user_id, kost_id, order_id, amount, status, snap_token) VALUES (?, ?, ?, ?, 'pending', ?)");
//     $stmt->bind_param("iisis", $user_id, $kost_id, $order_id, $harga, $snapToken);
//     $stmt->execute();

//     echo json_encode(['token' => $snapToken]);
// } catch (Exception $e) {
//     echo json_encode(['error' => 'Gagal generate token: ' . $e->getMessage()]);
// }

session_start();
require_once '../includes/db.php';
require_once '../vendor/autoload.php';
require_once '../config.php';

header('Content-Type: application/json');

// Validasi input
if (!isset($_SESSION['user_id']) || !isset($_POST['kost_id'])) {
    echo json_encode(['error' => 'Data tidak lengkap.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$kost_id = intval($_POST['kost_id']);
$_SESSION['last_kost_id'] = $kost_id;

// Ambil data kost
$result = $conn->query("SELECT * FROM kost WHERE id = $kost_id");
if (!$result || $result->num_rows === 0) {
    echo json_encode(['error' => 'Kost tidak ditemukan.']);
    exit;
}

$kost = $result->fetch_assoc();
$harga = (int)$kost['harga_per_bulan'];
$nama_kost = $kost['nama_kost'];
$order_id = 'ORDER-' . uniqid();

// Siapkan parameter Snap
$params = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => $harga
    ],
    'item_details' => [[
        'id' => $kost_id,
        'price' => $harga,
        'quantity' => 1,
        'name' => $nama_kost
    ]],
    'customer_details' => [
        'first_name' => $_SESSION['username'] ?? 'User',
        'email' => $_SESSION['email'] ?? 'customer@example.com'
    ]
];

try {
    $snapToken = \Midtrans\Snap::getSnapToken($params);

    // Jangan simpan ke database di sini!
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, kost_id, order_id, amount, status, snap_token) VALUES (?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("iisis", $user_id, $kost_id, $order_id, $harga, $snapToken);
    $stmt->execute();

    echo json_encode([
        'token' => $snapToken,
        'order_id' => $order_id,
        'kost_id' => $kost_id
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Gagal generate token: ' . $e->getMessage()]);
}
