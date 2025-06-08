<?php
$host = 'localhost';
$dbname = 'db_kost';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

function getKostById($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM kost WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getTransactionByOrderId($order_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM transaction WHERE order_id = ?");
    $stmt->bind_param("s", $order_id);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

?>
