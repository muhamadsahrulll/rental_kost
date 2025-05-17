<?php
session_start();
include 'includes/db.php';

// Ambil keyword pencarian jika ada
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
// Ambil parameter filter
$keyword    = isset($_GET['q']) ? trim($_GET['q']) : '';
$min_price  = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price  = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0;
$fasilitas  = isset($_GET['fasilitas']) ? $_GET['fasilitas'] : [];
$min_room   = isset($_GET['min_room']) ? (int)$_GET['min_room'] : 0;

// Build query dinamis untuk filter kost
$conditions = ["is_verified = 1"];
$params     = [];
$types       = '';

if ($keyword !== '') {
    $conditions[] = "(nama_kost LIKE ? OR lokasi LIKE ?)";
    $like         = "%" . $conn->real_escape_string($keyword) . "%";
    $params[]     = $like;
    $params[]     = $like;
    $types       .= 'ss';
}
if ($min_price > 0) {
    $conditions[] = "harga_per_bulan >= ?";
    $params[]     = $min_price;
    $types       .= 'i';
}
if ($max_price > 0) {
    $conditions[] = "harga_per_bulan <= ?";
    $params[]     = $max_price;
    $types       .= 'i';
}
if ($min_room > 0) {
    $conditions[] = "kamar_tersisa >= ?";
    $params[]     = $min_room;
    $types       .= 'i';
}
if (!empty($fasilitas) && is_array($fasilitas)) {
    foreach ($fasilitas as $f) {
        $conditions[] = "FIND_IN_SET(?, fasilitas_kamar)";
        $params[]     = $f;
        $types       .= 's';
    }
}

$sql = "SELECT * FROM kost WHERE " . implode(' AND ', $conditions) . " ORDER BY id DESC";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$kost_result = $stmt->get_result();
$total       = $kost_result->num_rows;

// Handle login submission
$login_error = '';
if (isset($_POST['login'])) {
    $email    = $_POST['email'];
    $password = $_POST['password'];
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
                if ($user['role'] == 'user')       header("Location: views/dashboard_user.php");
                elseif ($user['role'] == 'pemilik') header("Location: views/dashboard_pemilik.php");
                else                                header("Location: views/dashboard_admin.php");
                exit;
            }
            $login_error = 'Email Anda belum diverifikasi.';
        } else {
            $login_error = 'Password salah.';
        }
    } else {
        $login_error = 'Email tidak ditemukan.';
    }
}

// Query rekomendasi kost dengan filter nama atau lokasi jika pencarian
if ($keyword !== '') {
    $like = "%" . $conn->real_escape_string($keyword) . "%";
    $sql  = "SELECT * FROM kost WHERE is_verified = 1 AND (nama_kost LIKE ? OR lokasi LIKE ?) ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $kost_result = $stmt->get_result();
} else {
    $kost_result = $conn->query("SELECT * FROM kost WHERE is_verified = 1 ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rental Kost - Booking Kos Online</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="./assets/css/style.css" rel="stylesheet">
</head>
<body>
  
  <!-- Navbar + Search -->
 <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="index.php">Rental Kost</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navContent">
      <div class="d-flex ms-auto align-items-center">
        <!-- Search & Filter seperti biasa -->
        <form class="d-flex me-2" action="index.php" method="GET">
          <input type="text" name="q" class="form-control" placeholder="Cari Kost..." value="<?= htmlspecialchars($keyword) ?>">
          <button class="btn btn-outline-success ms-2" type="submit">
            <i class="bi bi-search"></i>
          </button>
        <form class="d-flex me-2" action="index.php" method="GET"></form>
        <button class="btn btn-outline-success me-2" type="button" data-bs-toggle="collapse" data-bs-target="#filterBox">
          <i class="bi bi-funnel-fill"></i> Filter
        </button>


          <?php if (isset($_SESSION['user_id'])): ?>
          <div class="dropdown ms-3">
            <a href="#" class="d-flex align-items-center text-success text-decoration-none"
              id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle fs-4"></i>
              <span class="ms-2"><?= htmlspecialchars($_SESSION['username']) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
              <?php if ($_SESSION['role']==='user'): ?>
                <li><a class="dropdown-item" href="views/dashboard_user.php">Dashboard</a></li>
              <?php elseif ($_SESSION['role']==='pemilik'): ?>
                <li><a class="dropdown-item" href="views/dashboard_pemilik.php">Dashboard</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
            Masuk
          </button>
        <?php endif; ?>



      </div>
    </div>
  </div>
</nav>

<!-- Filter Collapse -->
<div class="collapse bg-light py-3" id="filterBox">
  <div class="container">
    <form class="row g-3" action="index.php" method="GET">
      <input type="hidden" name="q" value="<?= htmlspecialchars($keyword) ?>">
      <div class="col-md-3">
        <label class="form-label">Min Harga</label>
        <input name="min_price" type="number" value="<?= $min_price ?>" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Max Harga</label>
        <input name="max_price" type="number" value="<?= $max_price ?>" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Kamar ≥</label>
        <input name="min_room" type="number" value="<?= $min_room ?>" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Fasilitas</label>
        <select name="fasilitas[]" class="form-select" multiple>
          <option <?= in_array('AC',$fasilitas)?'selected':'' ?>>AC</option>
          <option <?= in_array('Wifi',$fasilitas)?'selected':'' ?>>Wifi</option>
          <option <?= in_array('Kamar Mandi Dalam',$fasilitas)?'selected':'' ?>>K. Mandi Dalam</option>
        </select>
      </div>
      <div class="col-12 text-end">
        <button class="btn btn-primary" type="submit">Terapkan Filter</button>
      </div>
    </form>
  </div>
</div>
  <!-- Promo Slider -->
  <div id="promoCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="assets/images/promo1.jpg" class="d-block w-100" alt="Promo 1">
      </div>
      <div class="carousel-item">
        <img src="assets/images/promo2.jpg" class="d-block w-100" alt="Promo 2">
      </div>
      <div class="carousel-item">
        <img src="assets/images/promo3.png" class="d-block w-100" alt="Promo 3">
      </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>

  <!-- Daftarkan Kos Section -->
  <section class="container py-5">
    <div class="p-4 border rounded-4 text-center bg-white shadow-sm">
      <h4 class="mb-2 text-black">Bingung cara bayar kost di rentalkost?</h4>
      <p class="mb-4">Bisa kunjungi link dibawah ini untuk cara pembayaran yah!</p>
      <a href="#" class="btn btn-primary mt-2">Cara Bayar</a>
    </div>
  </section>

  <!-- Tutorial Bayar Section -->
  <section class="container py-5">
    <div class="p-4 border rounded-4 text-center bg-light shadow-sm">
      <h4 class="mb-2 text-black">Daftarkan Kos Anda di <span class="text-success">rentalkost</span></h4>
      <p class="mb-4">Berbagai fitur dan layanan untuk meningkatkan bisnis kos Anda</p>
      <a href="views/register.php?role=pemilik" class="btn btn-primary mt-2">Daftar sebagai Pemilik Kost</a>
    </div>
  </section>


  <!-- Rekomendasi Kost / Hasil Filter -->
  <section class="container py-5">
  <?php if (isset($_GET['search']) && $_GET['search'] !== ''): ?>
  <h3>Menampilkan <?= $total ?> hasil</h3>
<?php else: ?>
  <h3>Rekomendasi Kost Kamu</h3>
<?php endif; ?>

  <?php if ($total===0): ?>
    <p>Tidak ada kost ditemukan.</p>
  <?php else: ?>
  <div class="row">
      <?php while ($kost = $kost_result->fetch_assoc()): ?>
    <?php $imgs=json_decode($kost['list_gambar'],true); $first=$imgs[0]??'default.jpg'; ?>
    <div class="col-md-4 mb-4">
      <div class="card h-100">
        <img src="uploads/<?= htmlspecialchars($first) ?>" class="card-img-top" style="height:200px;object-fit:cover;">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?= htmlspecialchars($kost['nama_kost']) ?></h5>
          <p class="mt-auto">Rp<?= number_format($kost['harga_per_bulan'],0,',','.') ?> / bulan</p>
          <a href="views/detail_kost.php?id=<?= $kost['id'] ?>" class="btn btn-primary mt-2">Detail</a>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
  </div>
  <?php endif; ?>
</section>

  <!-- Footer -->
  <footer class="bg-dark text-white mt-5">
  <div class="container py-4">
    <div class="row">
      <div class="col-md-6 mb-3">
        <h5>Tentang Kami</h5>
        <p>Rental Kost adalah platform penyewaan dan penjualan kost terpercaya. Kami menghubungkan pencari kost dengan pemilik properti secara mudah dan efisien.</p>
      </div>
      <div class="col-md-6 mb-3">
        <h5>Hubungi Kami</h5>
        <ul class="list-unstyled">
          <li><i class="bi bi-envelope"></i> Email: info@rentalkost.com</li>
          <li><i class="bi bi-instagram"></i> Instagram: @rentalkost</li>
          <li><i class="bi bi-facebook"></i> Facebook: Rental Kost</li>
        </ul>
      </div>
    </div>
    <hr class="bg-secondary">
    <p class="text-center">&copy; <?= date('Y') ?> Rental Kost. All rights reserved.</p>
  </div>
</footer>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="loginModalLabel">Pilih Akun</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <!-- Nav pills -->
        <ul class="nav nav-pills mb-3 justify-content-center" id="loginTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="user-tab" data-bs-toggle="pill" data-bs-target="#userLogin" type="button" role="tab" aria-controls="userLogin" aria-selected="true">
              <i class="bi bi-person-fill me-1"></i> User
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="owner-tab" data-bs-toggle="pill" data-bs-target="#ownerLogin" type="button" role="tab" aria-controls="ownerLogin" aria-selected="false">
              <i class="bi bi-building me-1"></i> Pemilik Kost
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="admin-tab" data-bs-toggle="pill" data-bs-target="#adminLogin" type="button" role="tab" aria-controls="adminLogin" aria-selected="false">
              <i class="bi bi-shield-lock-fill me-1"></i> Admin
            </button>
          </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
          <!-- User Login Form -->
          <div class="tab-pane fade show active" id="userLogin" role="tabpanel" aria-labelledby="user-tab">
            <form action="views/login.php" method="POST">
              <input type="hidden" name="role" value="user">
              <div class="mb-3">
                <label for="userEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="userEmail" name="email" required>
              </div>
              <div class="mb-3">
                <label for="userPass" class="form-label">Password</label>
                <input type="password" class="form-control" id="userPass" name="password" required>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <a href="views/forgot_password.php" class="small">Lupa Password?</a>
                <button type="submit" name="login" class="btn btn-primary">Masuk</button>
              </div>
              <!-- Link Register User -->
              <p class="text-center small mb-0">
                Belum punya akun User? 
                <a href="views/register.php?role=user">Daftar di sini</a>
              </p>
            </form>
          </div>
          <!-- Pemilik Kost Login Form -->
          <div class="tab-pane fade" id="ownerLogin" role="tabpanel" aria-labelledby="owner-tab">
            <form action="views/login.php" method="POST">
              <input type="hidden" name="role" value="pemilik">
              <div class="mb-3">
                <label for="ownerEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="ownerEmail" name="email" required>
              </div>
              <div class="mb-3">
                <label for="ownerPass" class="form-label">Password</label>
                <input type="password" class="form-control" id="ownerPass" name="password" required>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <a href="views/forgot_password.php" class="small">Lupa Password?</a>
                <button type="submit" name="login" class="btn btn-dark">Masuk</button>
              </div>
              <!-- Link Register Pemilik -->
              <p class="text-center small mb-0">
                Belum punya akun Pemilik Kost? 
                <a href="views/register.php?role=pemilik">Daftar di sini</a>
              </p>
            </form>
          </div>
          <!-- Admin Login Form -->
          <div class="tab-pane fade" id="adminLogin" role="tabpanel" aria-labelledby="admin-tab">
            <form action="views/login.php" method="POST">
              <input type="hidden" name="role" value="admin">
              <div class="mb-3">
                <label for="adminEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="adminEmail" name="email" required>
              </div>
              <div class="mb-3">
                <label for="adminPass" class="form-label">Password</label>
                <input type="password" class="form-control" id="adminPass" name="password" required>
              </div>
              <div class="d-flex justify-content-between align-items-center">
                <a href="views/forgot_password.php" class="small">Lupa Password?</a>
                <button type="submit" name="login" class="btn btn-success">Masuk</button>
              </div>
              <!-- Tidak ada link register untuk Admin -->
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>