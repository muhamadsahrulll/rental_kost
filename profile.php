<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

include 'includes/db.php';

// Ambil data user dari database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, pekerjaan, jenis_kelamin, alamat FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Update profile jika form di-submit
$profileUpdated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $pekerjaan = $_POST['pekerjaan'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = $_POST['alamat'];

    $update_stmt = $conn->prepare("UPDATE users SET username = ?, pekerjaan = ?, jenis_kelamin = ?, alamat = ? WHERE id = ?");
    $update_stmt->bind_param("ssssi", $username, $pekerjaan, $jenis_kelamin, $alamat, $user_id);
    $update_stmt->execute();

    $_SESSION['username'] = $username;
    $profileUpdated = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Profile</title>
</head>
<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#">Profile</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/rental_kost/views/dashboard_user.php">Dashboard</a>
                    </li>
                </ul>
            </div>
        </nav>

        <h3>Profile Anda (<?= htmlspecialchars($_SESSION['role']); ?>)</h3>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']); ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="pekerjaan" class="form-label">Pekerjaan</label>
                <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" value="<?= htmlspecialchars($user['pekerjaan']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="Laki-laki" <?= ($user['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="Perempuan" <?= ($user['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control" id="alamat" name="alamat" rows="1" required><?= htmlspecialchars($user['alamat']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </form>

        <?php if ($profileUpdated): ?>
        <div class="alert alert-success mt-3">Profil berhasil diperbarui!</div>
        <?php endif; ?>
    </div>
</body>
</html>
