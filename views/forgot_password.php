<?php
session_start();
include '../includes/db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Cek apakah email ada di database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate reset token
        $token = bin2hex(random_bytes(3));
        $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Link reset
        $reset_link = "http://localhost/rental_kost/views/reset_password.php?token=" . $token;

        // Kirim email
        require '../vendor/autoload.php';
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ryankeviinnurhakim@gmail.com';
            $mail->Password   = 'wsghjisdrutdwmhh';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->setFrom('ryankeviinnurhakim@gmail.com','RentalKost');
            $mail->addAddress($email);
            $mail->Subject = 'Reset Password';
            $mail->Body    = "Klik link berikut untuk reset password Anda:\n$reset_link";
            $mail->send();
            $message = "Link reset password telah dikirim ke email Anda.";
        } catch (Exception $e) {
            $error = "Gagal mengirim email: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Lupa Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="./assets/css/style.css" rel="stylesheet">
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold text-success" href="../index.php">RentalKost</a>
      <div class="d-flex">
        <a class="nav-link text-success" href="#">About Us</a>
      </div>
    </div>
  </nav>

  <!-- Forgot Password Content -->
  <div class="container-fluid login-container">
    <div class="row justify-content-center">
      <div class="col-lg-4 d-flex">
        <div class="login-box p-5 w-100">
          
          <h2 class="mb-4 text-center">Lupa Password</h2>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php elseif (!empty($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
          <?php endif; ?>

          <form action="forgot_password.php" method="POST">
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Kirim Link Reset</button>
          </form>

          <div class="mt-3 text-center">
            <a href="login.php" class="small">Kembali ke Login</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
