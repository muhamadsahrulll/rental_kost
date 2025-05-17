<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verification_code = $_POST['verification_code'];

    $stmt = $conn->prepare("SELECT user_id FROM email_verification WHERE verification_code = ?");
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];

        // Update is_verified menjadi 1
        $u = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
        $u->bind_param("i", $user_id);
        $u->execute();

        $message = "Email berhasil diverifikasi! <a href='login.php'>Login</a>";
        header('Location: verifikasi_email.php?status=success');
        $alertClass = "alert-success";
    } else {
        $message = "Kode verifikasi salah.";
        $alertClass = "alert-danger";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Verifikasi Email</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="./assets/css/style.css" rel="stylesheet">
</head>
<body>

  <div class="container-fluid login-container">
    <div class="row justify-content-center">
      <div class="col-lg-4 d-flex">
        <div class="login-box p-5 w-100">
          <h2 class="mb-4 text-center">Verifikasi Email</h2>

          <?php if (isset($message)): ?>
            <div class="alert <?= $alertClass ?>"><?= $message ?></div>
          <?php endif; ?>

          <form action="verify_email.php" method="POST">
            <div class="mb-3">
              <label for="verification_code" class="form-label">Kode Verifikasi</label>
              <input type="text" id="verification_code" name="verification_code" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verifikasi</button>
          </form>

          
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
