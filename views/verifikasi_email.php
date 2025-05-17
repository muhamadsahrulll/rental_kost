<?php
$test = include '../includes/db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verification_code'])) {
        // Verifikasi email
        $verification_code = $_POST['verification_code'];

        $stmt = $conn->prepare("SELECT user_id FROM email_verification WHERE verification_code = ?");
        $stmt->bind_param("s", $verification_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['user_id'];

            // Update is_verified menjadi 1
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            //echo "Email berhasil diverifikasi! <a href='login.php'>Login</a>";
        } else {
            echo "Kode verifikasi salah.";
        }
    }

    if (isset($_POST['resend_verification'])) {
        // Resend email verification
        $email = $_POST['email']; // Assuming email is provided in a hidden input
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            $username = $user['username'];
            $verification_code = bin2hex(random_bytes(3));

            // Update kode verifikasi
            $stmt = $conn->prepare("UPDATE email_verification SET verification_code = ? WHERE user_id = ?");
            $stmt->bind_param("si", $verification_code, $user_id);
            $stmt->execute();

            // Kirim email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ryankeviinnurhakim@gmail.com';
                $mail->Password = 'wsghjisdrutdwmhh'; // Password aplikasi
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('ryankeviinnurhakim@gmail.com', 'SEWA KOST');
                $mail->addAddress($email);
                $mail->Subject = 'Verifikasi Email Anda';
                $mail->Body = "Halo $username,\n\nBerikut adalah kode verifikasi Anda:\n$verification_code\n\nSilakan masukkan kode ini untuk memverifikasi email Anda.";

                $mail->send();
                echo "Kode verifikasi telah dikirim ulang ke email Anda.";
            } catch (Exception $e) {
                echo "Gagal mengirim email. Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Email tidak ditemukan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Email Terverifikasi</title>
</head>
<body>
<div class="container mt-5">
    <div class="card text-center shadow">
        <div class="card-header bg-success text-white">
            <h3>Email Berhasil Diverifikasi</h3>
        </div>
        <div class="card-body">
            <p class="card-text">Selamat! Email Anda telah berhasil diverifikasi. Anda sekarang dapat melanjutkan ke halaman login.</p>
            <a href="login.php" class="btn btn-primary">Login</a>
        </div>
        <div class="card-footer text-muted">
            Terima kasih telah menggunakan layanan RentalKost.
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
