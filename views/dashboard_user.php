<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../views/login.php");
    exit;
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$message = '';
$alertClass = '';

$trans = $conn->query("SELECT t.*, k.nama_kost FROM transactions t JOIN kost k ON t.kost_id = k.id WHERE t.user_id = $user_id ORDER BY t.created_at DESC");

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username     = $_POST['username'];
    $email        = $_POST['email'];
    $pekerjaan    = $_POST['pekerjaan'];
    $jenis_kelamin= $_POST['jenis_kelamin'];
    $alamat       = $_POST['alamat'];

    $upd = $conn->prepare("
      UPDATE users 
      SET username = ?, email = ?, pekerjaan = ?, jenis_kelamin = ?, alamat = ?
      WHERE id = ?
    ");
    $upd->bind_param("sssssi", $username, $email, $pekerjaan, $jenis_kelamin, $alamat, $user_id);

    if ($upd->execute()) {
        $message = "Profile berhasil diperbarui.";
        $alertClass = "alert-success";
        // Update session username
        $_SESSION['username'] = $username;
    } else {
        $message = "Gagal memperbarui profile.";
        $alertClass = "alert-danger";
    }
}

// Ambil data user (terbaru)
$stmt = $conn->prepare("
  SELECT username, email, pekerjaan, jenis_kelamin, alamat
  FROM users WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="../index.php">Rental Kost</a>
    <div class="d-flex align-items-center">
      <i class="bi bi-person-circle fs-4 text-success"></i>
      <span class="ms-2 text-success fw-medium"><?= htmlspecialchars($_SESSION['username']) ?></span>
    </div>
  </div>
</nav>

<div class="container-fluid dashboard-container">
  <div class="row gx-0">
    <!-- Sidebar -->
    <nav class="col-md-3 col-lg-2 sidebar bg-light">
      <ul class="nav flex-column pt-4">
        <li class="nav-item">
          <a class="nav-link active" href="#" data-section="kosSaya">
            <i class="bi bi-house-door me-2"></i> Kos Saya
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-section="profile">
            <i class="bi bi-person me-2"></i> Profile
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" data-section="riwayat">
            <i class="bi bi-clock-history me-2"></i> Riwayat Transaksi
          </a>
        </li>
        <li class="nav-item mt-3">
          <a class="nav-link text-danger" href="../logout.php">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
          </a>
        </li>
      </ul>
    </nav>

    <!-- Main Content -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-5 pt-4">
      <div class="content-box">

        <!-- Notification -->
        <?php if ($message): ?>
          <div class="alert <?= $alertClass ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Kos Saya -->
        <section id="kosSaya" class="dashboard-section">
          <h3>Kos Saya</h3>
          <p>(Data dummy kos yang sudah disewa muncul di sini)</p>
          <ul class="list-group">
            <li class="list-group-item">Kos A — 12 Apr 2025 s/d 12 Mei 2025</li>
            <li class="list-group-item">Kos B — 05 Mar 2025 s/d 05 Apr 2025</li>
          </ul>
        </section>

        <!-- Profile -->
        <section id="profile" class="dashboard-section d-none">
          <h3>Profile Saya</h3>
          <form action="dashboard_user.php" method="POST">
            <input type="hidden" name="update_profile" value="1">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control"
                     value="<?= htmlspecialchars($user['username']) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control"
                     value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>
            <div class="mb-3">
              <label class="form-label">Pekerjaan</label>
              <input type="text" name="pekerjaan" class="form-control"
                     value="<?= htmlspecialchars($user['pekerjaan'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Jenis Kelamin</label>
              <select name="jenis_kelamin" class="form-select">
                <option <?= $user['jenis_kelamin']=='Laki-laki'?'selected':'' ?>>Laki-laki</option>
                <option <?= $user['jenis_kelamin']=='Perempuan'?'selected':'' ?>>Perempuan</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Alamat</label>
              <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
            </div>
            <button class="btn btn-primary">Simpan</button>
          </form>
        </section>

        <!-- Riwayat Transaksi -->
        <section id="riwayat" class="dashboard-section d-none">
          <h3>Riwayat Transaksi</h3>
          <!-- <p>(Data dummy riwayat muncul di sini)</p>
          <ul class="list-group">
            <li class="list-group-item">Transaksi #123 — Rp1.200.000</li>
            <li class="list-group-item">Transaksi #124 — Rp800.000</li>
          </ul> -->
          <table class="table">
              <thead>
                  <tr class="text-center">
                      <th>Kost</th>
                      <th>Status</th>
                      <th>Total</th>
                      <th>Aksi</th>
                  </tr>
              </thead>
              <tbody>
                  <?php while ($row = $trans->fetch_assoc()): ?>
                  <tr>
                      <td><?= $row['nama_kost'] ?></td>
                      <td><?= htmlspecialchars($row['status']) ?></td>
                      <td>Rp<?= number_format($row['amount']) ?></td>
                        <?php
                        $refund_deadline = null;
                        if (!empty($row['settlement_time'])) {
                            $refund_deadline = strtotime($row['settlement_time']) + 2; // 2 detik
                        }
                        $now = time();
                        ?>

                        <td class="text-center">
                            <?php if (strtolower($row['status']) === 'pending' && !empty($row['snap_token'])): ?>
                                <button class="btn btn-primary btn-sm btn-sm px-2 py-0 rounded-3" onclick="lunasi('<?= $row['order_id'] ?>', <?= $row['kost_id'] ?>, '<?= $row['snap_token'] ?>')">Lunasi</button>
                                |
                                <button class="btn btn-danger btn-sm btn-sm px-2 py-0 rounded-3" onclick="cancel('<?= $row['id'] ?>')">Cancel</button>

                            <?php elseif ($row['status'] === 'settlement'): ?>
                                <?php if ($refund_deadline && $now < $refund_deadline): ?>
                                    <button class="btn btn-success btn-sm btn-sm px-2 py-0 rounded-3" disabled>Dibayar</button>
                                    |
                                    <button class="btn btn-warning btn-sm btn-sm px-2 py-0 rounded-3" onclick="refund('<?= $row['id'] ?>')">Refund</button>
                                <?php endif; ?>

                            <?php elseif ($row['status'] === 'refund' || $row['status'] === 'refund_manual'): ?>
                                <span class="btn btn-px-2 btn-sm px-2 py-0 rounded-3 disabled">Refunded</span>

                            <?php elseif ($row['status'] === 'cancelled'): ?>
                                <span class="btn btn-danger btn-sm px-2 py-0 rounded-3 disabled">Dibatalkan</span>

                            <?php else: ?>
                                <span class="btn btn-secondary btn-sm px-2 py-0 rounded-3 disabled"><?= ucfirst($row['status']) ?></span>
                            <?php endif; ?>
                        </td>
                  </tr>
                  <?php endwhile; ?>
              </tbody>
          </table>
        </section>
      </div>
    </main>
  </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-uESNPW0kWFz4AtZD"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
  link.addEventListener('click', e => {
    const sectionId = link.dataset.section;

    // Hanya jalankan tab switching jika memiliki data-section
    if (sectionId) {
      e.preventDefault();
      document.querySelectorAll('.sidebar .nav-link').forEach(a => a.classList.remove('active'));
      document.querySelectorAll('.dashboard-section').forEach(s => s.classList.add('d-none'));
      link.classList.add('active');
      const section = document.getElementById(sectionId);
      if (section) {
        section.classList.remove('d-none');
      }
    }
    // Kalau tidak punya data-section (contoh: Logout), biarkan browser lanjutkan default action
  });
});

function lunasi(order_id, kost_id, snap_token) {
    snap.pay(snap_token, {
        onSuccess: function(result) {
            // Simpan transaksi yang berhasil
            fetch('../helpers/midtrans/save_transaction.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    kost_id: kost_id,
                    order_id: order_id,
                    amount: result.gross_amount,
                    status: 'settlement',
                    snap_token: snap_token,
                    payment_type: result.payment_type
                })
            })
            .then(res => res.json())
            .then(res => {
                alert(res.message);
                window.location.reload(); // reload agar status dan tombol update
            })
            .catch(err => {
                console.error(err);
                alert('Gagal menyimpan transaksi!');
            });
        },
        onPending: function(result) {
            alert('Pembayaran masih pending.');
            window.location.reload();
        },
        onError: function(result) {
            alert('Pembayaran gagal: ' + JSON.stringify(result));
        },
        onClose: function() {
            alert('Anda menutup popup pembayaran sebelum selesai.');
        }
    });
}

// function refund(id) {
//   if (!confirm('Yakin ingin refund?')) return;
//   fetch('../helpers/midtrans/refund_transaction.php', {
//     method: 'POST',
//     headers: {'Content-Type': 'application/x-www-form-urlencoded'},
//     body: 'transaction_id=' + id
//   })
//   .then(res => res.json())
//   .then(data => {
//     alert(data.success ? data.message : 'Error: ' + data.message);
//     if (data.success) window.location.reload();
//   });
// }

function cancel(transactionId) {
    if (confirm('Yakin ingin membatalkan transaksi?')) {
        fetch('../helpers/midtrans/cancel_transaction.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({transaction_id: transactionId})
        })
        .then(res => res.json())
        .then(data => alert(data.message))
        .then(() => location.reload())
        .catch(() => alert('Terjadi kesalahan'));
    }
}

function refund(transactionId) {
    if (confirm('Yakin ingin melakukan refund?')) {
        fetch('../helpers/midtrans/refund_transaction.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({transaction_id: transactionId})
        })
        .then(res => res.json())
        .then(data => alert(data.message))
        // .then(() => location.reload())
        .catch(() => alert('Terjadi kesalahan'));
    }
}


</script>
</body>
</html>
