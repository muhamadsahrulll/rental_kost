<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    // Ambil data user berdasarkan email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 1) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                if ($user['role'] == 'user') {
                    header("Location: ../views/dashboard_user.php");
                } elseif ($user['role'] == 'pemilik') {
                    header("Location: ../views/dashboard_pemilik.php");
                } else {
                    header("Location: ../views/dashboard_admin.php");
                }
                exit;
            } else {
                $error = "Email Anda belum diverifikasi!";
            }
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Masuk</title>
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

  <!-- Login Content -->
  <div class="container-fluid login-container">
    <div class="row justify-content-center">
      <div class="col-lg-4">
        <div class="login-box p-5">
          <a href="../index.php" class="btn btn-white mb-3">
            <i class="bi bi-arrow-left"></i> Kembali
          </a>
          <h2 class="mb-4">Masuk Akun Penyewa / Pemilik Kos</h2>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form action="login.php" method="POST">
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Masuk</button>
          </form>

          <div class="mt-3 d-flex justify-content-between">
            <a href="forgot_password.php" class="small">Lupa Password?</a>
            <a href="register.php" class="small">Daftar Baru</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
