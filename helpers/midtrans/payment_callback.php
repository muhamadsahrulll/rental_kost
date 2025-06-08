<?php
require_once '../../includes/db.php';
require_once '../../config.php';
require_once '../../vendor/autoload.php';

$notif = new \Midtrans\Notification();
$transaction = $notif->transaction_status;
$order_id = $notif->order_id;

$db_transaction = getTransactionByOrderId($order_id);
if (!$db_transaction) die('Transaksi tidak ditemukan');

try {
    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE transaction SET status = ?, updated_at = NOW() WHERE order_id = ?");
    $stmt->execute([$transaction, $order_id]);

    if (in_array($transaction, ['cancel', 'deny', 'expire', 'refund'])) {
        $stmt = $db->prepare("UPDATE kost SET kamar_tersisa = kamar_tersisa + 1 WHERE id = ?");
        $stmt->execute([$db_transaction['kost_id']]);
    }

    $db->commit();
    echo "OK";
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo "Error processing callback";
}
