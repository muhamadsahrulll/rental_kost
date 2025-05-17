<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pemilik') {
    header("Location: ../views/login.php");
    exit;
}
include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// ─── HANDLE UPDATE PROFILE ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username      = $_POST['username'];
    $email         = $_POST['email'];
    $pekerjaan     = $_POST['pekerjaan'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat        = $_POST['alamat'];

    $upd = $conn->prepare("
      UPDATE users 
      SET username = ?, email = ?, pekerjaan = ?, jenis_kelamin = ?, alamat = ?
      WHERE id = ?
    ");
    $upd->bind_param("sssssi", $username, $email, $pekerjaan, $jenis_kelamin, $alamat, $user_id);

    if ($upd->execute()) {
        // simpan ulang username di session (navbar)
        $_SESSION['username'] = $username;
        $_SESSION['msg']      = "Profile berhasil diperbarui.";
        $_SESSION['msgClass'] = "alert-success";
    } else {
        $_SESSION['msg']      = "Gagal memperbarui profile.";
        $_SESSION['msgClass'] = "alert-danger";
    }
    header("Location: dashboard_pemilik.php");
    exit;
}
// ─── HANDLE EDIT KOST ───────────────────────────────────────────
if (isset($_POST['edit_kost'])) {
    $id            = $_POST['kost_id'];
    $nama_kost     = $_POST['nama_kost'];
    $harga         = $_POST['harga_per_bulan'];
    $kamar         = $_POST['kamar_tersisa'];
    $lokasi        = $_POST['lokasi'];
    $deskripsi     = $_POST['deskripsi'];
    $fasilitas     = $_POST['fasilitas_kamar'];

    $stmt = $conn->prepare("
        UPDATE kost
        SET nama_kost = ?, harga_per_bulan = ?, kamar_tersisa = ?, lokasi = ?, deskripsi = ?, fasilitas_kamar = ?
        WHERE id = ? AND pemilik_id = ?
    ");
    $stmt->bind_param("siisssii", $nama_kost, $harga, $kamar, $lokasi, $deskripsi, $fasilitas, $id, $user_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Kost berhasil diperbarui.";
        $_SESSION['msgClass'] = "alert-success";
    } else {
        $_SESSION['msg'] = "Gagal memperbarui kost.";
        $_SESSION['msgClass'] = "alert-danger";
    }
    header("Location: dashboard_pemilik.php");
    exit;
}

// ─── HANDLE DELETE KOST ─────────────────────────────────────────
if (isset($_POST['delete_kost'])) {
    $id = $_POST['kost_id'];

    $stmt = $conn->prepare("DELETE FROM kost WHERE id = ? AND pemilik_id = ?");
    $stmt->bind_param("ii", $id, $user_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Kost berhasil dihapus.";
        $_SESSION['msgClass'] = "alert-success";
    } else {
        $_SESSION['msg'] = "Gagal menghapus kost.";
        $_SESSION['msgClass'] = "alert-danger";
    }
    header("Location: dashboard_pemilik.php");
    exit;
}
// ─── HANDLE UPDATE GAMBAR KOST ─────────────────────────────────
if (isset($_POST['update_gambar'])) {
    $id = $_POST['kost_id'];
    $target_dir = "../uploads/";
    $gambar_names = [];

    foreach ($_FILES['list_gambar']['name'] as $key => $name) {
        $tmp_name = $_FILES['list_gambar']['tmp_name'][$key];
        $target_file = $target_dir . basename($name);
        if (move_uploaded_file($tmp_name, $target_file)) {
            $gambar_names[] = basename($name);
        }
    }

    $gambar_list = json_encode($gambar_names);

    $stmt = $conn->prepare("UPDATE kost SET list_gambar = ? WHERE id = ? AND pemilik_id = ?");
    $stmt->bind_param("sii", $gambar_list, $id, $user_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Gambar berhasil diperbarui.";
        $_SESSION['msgClass'] = "alert-success";
    } else {
        $_SESSION['msg'] = "Gagal memperbarui gambar.";
        $_SESSION['msgClass'] = "alert-danger";
    }
    header("Location: dashboard_pemilik.php");
    exit;
}

// ─── AMBIL DATA USER UNTUK FORM ─────────────────────────────────
$stmt_user = $conn->prepare("
  SELECT username, email, pekerjaan, jenis_kelamin, alamat
  FROM users 
  WHERE id = ?
");

$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();

// ─── STATS ──────────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT COUNT(*) FROM kost WHERE pemilik_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_kost = $stmt->get_result()->fetch_row()[0];

$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM reviews r 
    JOIN kost k ON r.kost_id = k.id 
    WHERE k.pemilik_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_reviews = $stmt->get_result()->fetch_row()[0];

// ─── PERMINTAAN SEWA (dummy) ───────────────────────────────────
$requests = []; // nanti dari DB

// ─── DAFTAR KOST ───────────────────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM kost WHERE pemilik_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$kosts = $stmt->get_result();
// ─── TAMBAH KOST ───────────────────────────────────────────────
// Cek apakah ada semua input yang dibutuhkan
if (isset($_POST['add_kost']) && isset($_POST['fasilitas_kamar']) && isset($_FILES['list_gambar'])) {
    $nama_kost      = $_POST['nama_kost'] ?? '';
    $harga_per_bulan= $_POST['harga_per_bulan'] ?? 0;
    $kamar_tersisa  = $_POST['kamar_tersisa'] ?? 0;
    $lokasi         = $_POST['lokasi'] ?? '';
    $deskripsi      = $_POST['deskripsi'] ?? '';
    $fasilitas      = $_POST['fasilitas_kamar'] ?? '';

    $target_dir = "../uploads/";
    $gambar_names = [];

    foreach ($_FILES['list_gambar']['name'] as $key => $name) {
        $tmp_name = $_FILES['list_gambar']['tmp_name'][$key];
        $target_file = $target_dir . basename($name);
        if (move_uploaded_file($tmp_name, $target_file)) {
            $gambar_names[] = basename($name);
        }
    }

    $gambar_list = json_encode($gambar_names);

    $stmt = $conn->prepare("INSERT INTO kost 
        (pemilik_id, nama_kost, harga_per_bulan, kamar_tersisa, lokasi, deskripsi, fasilitas_kamar, list_gambar) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisssss", $user_id, $nama_kost, $harga_per_bulan, $kamar_tersisa, $lokasi, $deskripsi, $fasilitas, $gambar_list);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Kost berhasil ditambahkan.";
        $_SESSION['msgClass'] = "alert-success";
    } else {
        $_SESSION['msg'] = "Gagal menambahkan kost.";
        $_SESSION['msgClass'] = "alert-danger";
    }

    header("Location: dashboard_pemilik.php");
    exit;
}



// ─── REVIEW KOST PEMILIK ──────────────────────────────────────
$stmt = $conn->prepare("
    SELECT r.*, u.username, k.nama_kost 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN kost k ON r.kost_id = k.id 
    WHERE k.pemilik_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reviews = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard Pemilik</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <style>
    /* Hanya override ringan bila dibutuhkan */
    .pemilik-container { display: flex; }
    .pemilik-sidebar { width: 250px; min-height: calc(100vh - 56px); }
    .pemilik-main    { flex:1; }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand text-success fw-bold" href="../index.php">Rental Kost</a>
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navContent">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navContent">
        <div class="ms-auto d-flex align-items-center">
          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-success text-decoration-none"
               data-bs-toggle="dropdown">
              <i class="bi bi-person-circle fs-4"></i>
              <span class="ms-2"><?= htmlspecialchars($_SESSION['username']) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <div class="pemilik-container">
    <!-- Sidebar -->
    <aside class="pemilik-sidebar bg-dark text-white p-3">
      <h4>Pemilik Panel</h4>
      <ul class="nav flex-column">
        <li class="nav-item"><a href="#" class="nav-link text-white active" data-section="sect-dashboard">Dashboard</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-white" data-section="sect-profile">Profile</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-white" data-section="sect-requests">Permintaan Sewa</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-white" data-section="sect-kosts">Daftar Kost</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-white" data-section="sect-reviews">Review</a></li>
        <li class="nav-item mt-3"><a href="../logout.php" class="nav-link text-danger">Logout</a></li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="pemilik-main p-4">
      <!-- 1. Dashboard -->
      <section id="sect-dashboard" class="pemilik-section active">
        <h2>Dashboard</h2>
        <div class="row g-3 mt-3">
          <div class="col-md-6">
            <div class="p-4 bg-success text-white rounded-3">
              <h1><?= $total_kost ?></h1>
              <p>Total Kost</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-4 bg-danger text-white rounded-3">
              <h1><?= $total_reviews ?></h1>
              <p>Total Reviews</p>
            </div>
          </div>
        </div>
      </section>

      <!-- 2. Profile -->
      <section id="sect-profile" class="pemilik-section">
        <h2>Profile</h2>
        <?php if (!empty($_SESSION['msg'])): ?>
          <div class="alert <?= $_SESSION['msgClass'] ?>">
            <?= htmlspecialchars($_SESSION['msg']) ?>
          </div>
          <?php 
            unset($_SESSION['msg'], $_SESSION['msgClass']);
          ?>
        <?php endif; ?>
        <form action="dashboard_pemilik.php" method="POST">
          <input type="hidden" name="update_profile" value="1">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control"
                   value="<?= htmlspecialchars($user_data['username'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Pekerjaan</label>
            <input type="text" name="pekerjaan" class="form-control"
                   value="<?= htmlspecialchars($user_data['pekerjaan'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-select">
              <option value="Laki-laki" <?= ($user_data['jenis_kelamin'] ?? '')==='Laki-laki'?'selected':'' ?>>Laki-laki</option>
              <option value="Perempuan" <?= ($user_data['jenis_kelamin'] ?? '')==='Perempuan'?'selected':'' ?>>Perempuan</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($user_data['alamat'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>

      </section>

      <!-- 3. Permintaan Sewa -->
      <section id="sect-requests" class="pemilik-section">
        <h2>Permintaan Sewa Baru</h2>
        <?php if (empty($requests)): ?>
          <p>Tidak ada permintaan sewa baru.</p>
        <?php else: ?>
          <ul class="list-group">
            <?php foreach($requests as $req): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($req['nama_user']) ?> &mdash; <?= htmlspecialchars($req['nama_kost']) ?>
                <span>
                  <button class="btn btn-sm btn-success">Terima</button>
                  <button class="btn btn-sm btn-danger">Tolak</button>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <!-- 4. Daftar Kost -->
<section id="sect-kosts" class="pemilik-section">
  <h2>Daftar Kost Anda</h2>

  <!-- Tombol Tambah Kost -->
  <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addKostModal">
    <i class="bi bi-plus-circle"></i> Tambah Kost
  </button>

  <?php if (isset($_SESSION['msg'])): ?>
    <div class="alert <?= $_SESSION['msgClass'] ?>">
      <?= $_SESSION['msg']; unset($_SESSION['msg'], $_SESSION['msgClass']); ?>
    </div>
  <?php endif; ?>

  <table class="table table-bordered table-striped">
    <thead class="table-success">
      <tr>
        <th>Nama Kost</th>
        <th>Harga/Bulan</th>
        <th>Kamar Tersisa</th>
        <th>Lokasi</th>
        <th>Fasilitas</th>
        <th>Gambar</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $kosts->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['nama_kost']) ?></td>
          <td>Rp<?= number_format($row['harga_per_bulan']) ?></td>
          <td><?= $row['kamar_tersisa'] ?></td>
          <td><?= htmlspecialchars($row['lokasi']) ?></td>
          <td><?= htmlspecialchars($row['fasilitas_kamar']) ?></td>
          <td>
            <?php
              $imgs = json_decode($row['list_gambar'], true);
              if ($imgs) {
                echo '<img src="../uploads/' . htmlspecialchars($imgs[0]) . '" width="80">';
              } else {
                echo '-';
              }
            ?>
          </td>
          <td>
            <!-- Tombol Edit -->
            <button class="btn btn-sm btn-warning mb-1" data-bs-toggle="collapse" data-bs-target="#editForm<?= $row['id'] ?>">Edit</button>

            <!-- Tombol Ganti Gambar -->
            <button class="btn btn-sm btn-secondary mb-1" data-bs-toggle="collapse" data-bs-target="#gambarForm<?= $row['id'] ?>">Gambar</button>

            <!-- Form Delete -->
            <form method="post" onsubmit="return confirm('Yakin ingin menghapus kost ini?')" class="d-inline">
              <input type="hidden" name="kost_id" value="<?= $row['id'] ?>">
              <button type="submit" name="delete_kost" class="btn btn-sm btn-danger">Hapus</button>
            </form>
          </td>
        </tr>

        <!-- Form Edit -->
        <tr class="collapse" id="editForm<?= $row['id'] ?>">
          <td colspan="7">
            <form method="post">
              <input type="hidden" name="kost_id" value="<?= $row['id'] ?>">
              <div class="row g-2">
                <div class="col-md-3"><input type="text" name="nama_kost" class="form-control" placeholder="Nama Kost" value="<?= htmlspecialchars($row['nama_kost']) ?>" required></div>
                <div class="col-md-2"><input type="number" name="harga_per_bulan" class="form-control" placeholder="Harga" value="<?= $row['harga_per_bulan'] ?>" required></div>
                <div class="col-md-2"><input type="number" name="kamar_tersisa" class="form-control" placeholder="Kamar" value="<?= $row['kamar_tersisa'] ?>" required></div>
                <div class="col-md-2"><input type="text" name="lokasi" class="form-control" placeholder="Lokasi" value="<?= htmlspecialchars($row['lokasi']) ?>" required></div>
                <div class="col-md-3"><input type="text" name="fasilitas_kamar" class="form-control" placeholder="Fasilitas" value="<?= htmlspecialchars($row['fasilitas_kamar']) ?>"></div>
              </div>
              <div class="mt-2">
                <textarea name="deskripsi" class="form-control" placeholder="Deskripsi"><?= htmlspecialchars($row['deskripsi']) ?></textarea>
              </div>
              <button type="submit" name="edit_kost" class="btn btn-success mt-2">Simpan Perubahan</button>
            </form>
          </td>
        </tr>

        <!-- Form Ganti Gambar -->
        <tr class="collapse" id="gambarForm<?= $row['id'] ?>">
          <td colspan="7">
            <form method="post" enctype="multipart/form-data">
              <input type="hidden" name="kost_id" value="<?= $row['id'] ?>">
              <div class="mb-2">
                <label for="gambar">Ganti Gambar (bisa lebih dari 1)</label>
                <input type="file" name="list_gambar[]" multiple class="form-control" required>
              </div>
              <button type="submit" name="update_gambar" class="btn btn-primary">Update Gambar</button>
            </form>
          </td>
        </tr>

      <?php endwhile; ?>
    </tbody>
  </table>
</section>


      <!-- 5. Review -->
      <section id="sect-reviews" class="pemilik-section">
        <h2>Review Kost Anda</h2>
        <?php if ($reviews->num_rows===0): ?>
          <p>Belum ada review.</p>
        <?php else: ?>
          <?php while($r = $reviews->fetch_assoc()): ?>
            <div class="card mb-3">
              <div class="card-body">
                <strong><?= htmlspecialchars($r['username']) ?></strong>  
                di <em><?= htmlspecialchars($r['nama_kost']) ?></em><br>
                <?php for($i=0;$i<$r['rating'];$i++): ?>
                  <span class="text-warning">★</span>
                <?php endfor; ?>
                <p class="mt-2"><?= htmlspecialchars($r['comment']) ?></p>
                <small class="text-muted"><?= date('d M Y',strtotime($r['created_at'])) ?></small>
              </div>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <!-- Modal Tambah Kost -->
<div class="modal fade" id="addKostModal" tabindex="-1" aria-labelledby="addKostLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="add_kost" value="1">
      <div class="modal-header">
        <h5 class="modal-title" id="addKostLabel">Tambah Kost Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nama Kost</label>
          <input type="text" name="nama_kost" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Harga per Bulan</label>
          <input type="number" name="harga_per_bulan" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Kamar Tersisa</label>
          <input type="number" name="kamar_tersisa" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Lokasi</label>
          <input type="text" name="lokasi" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Deskripsi</label>
          <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Fasilitas Kamar</label>
          <input type="text" name="fasilitas_kamar" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Gambar Kost (boleh lebih dari satu)</label>
          <input type="file" name="list_gambar[]" class="form-control" multiple required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.querySelectorAll(".nav-link[data-section]").forEach(link => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      document.querySelectorAll(".pemilik-section").forEach(sec => sec.classList.remove("active"));
      document.querySelectorAll(".nav-link").forEach(nav => nav.classList.remove("active"));
      const targetId = this.getAttribute("data-section");
      document.getElementById(targetId).classList.add("active");
      this.classList.add("active");
    });
  });
</script>

</body>
</html>
