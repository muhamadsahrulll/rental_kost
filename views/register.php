<?php
session_start();
include '../includes/db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role     = $_POST['role'];
    $code     = bin2hex(random_bytes(3));

    // Simpan ke DB
    $stmt = $conn->prepare("
      INSERT INTO users (username, email, password, role)
      VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $username, $email, $password, $role);

    if ($stmt->execute()) {
        $uid = $conn->insert_id;
        $v = $conn->prepare("
          INSERT INTO email_verification (user_id, verification_code)
          VALUES (?, ?)
        ");
        $v->bind_param("is", $uid, $code);
        $v->execute();

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
            $mail->Subject = 'Verifikasi Email';
            $mail->Body    = "Halo $username,\nKode verifikasi Anda: $code";
            $mail->send();
        } catch (Exception $e) {
            error_log($mail->ErrorInfo);
        }

        header('Location: ../views/verify_email.php');
        exit;
    } else {
        $error = "Email sudah terdaftar.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Daftar Akun</title>
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="./assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="../index.php">RentalKost</a>
    <div class="d-flex">
      <a class="nav-link text-success" href="#">About Us</a>
    </div>
  </div>
</nav>

<div class="container-fluid px-0 register-container">
  <div class="row gx-0 align-items-stretch justify-content-center">
    <!-- Form -->
    <div class="col-lg-5 d-flex">
      <div class="register-box p-5 w-100">
        <a href="../index.php" class="btn btn-white mb-3">
          <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h2 class="mb-4">Daftar Akun Penyewa / Pemilik Kos</h2>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
          <div class="mb-3">
            <label for="username" class="form-label">Nama Lengkap</label>
            <input type="text" id="username" name="username" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control">
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="role" class="form-label">Jenis Akun</label>
            <select id="role" name="role" class="form-select">
              <option value="user">User</option>
              <option value="pemilik">Pemilik Kost</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">Daftar</button>
        </form>

        <p class="mt-3 text-center">
          Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </p>
      </div>
    </div>

    <!-- Ilustrasi -->
    <div class="col-lg-5 d-none d-lg-block px-0">
      <img src="../assets/images/daftar.jpg"
           alt="Ilustrasi Daftar"
           class="img-fluid h-100 object-cover">
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
