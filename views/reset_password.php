<?php
include '../includes/db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Cek apakah token valid
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_password = $_POST['new_password'];
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update password
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
            $stmt->bind_param("ss", $hashed_password, $token);
            $stmt->execute();

            echo "Password berhasil direset. Anda dapat login dengan password baru.";
        }
    } else {
        echo "Token tidak valid.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Reset Password</title>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Reset Password</h2>
    <form action="reset_password.php?token=<?php echo $_GET['token']; ?>" method="POST">
        <div class="mb-3">
            <label>Password Baru</label>
            <input type="password" name="new_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
    <div class="mt-3 text-center">
        <a href="login.php" class="btn btn-link">Kembali ke Login</a>
    </div>
</div>
<script src="../assets/bootstrap.bundle.min.js"></script>
</body>
</html>
