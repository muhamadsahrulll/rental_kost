<?php
session_start();
include '../includes/db.php';

// 1. Ambil ID kost
if (!isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}
$kost_id = (int)$_GET['id'];

// 2. Ambil data kost
$stmt = $conn->prepare("SELECT * FROM kost WHERE id = ?");
$stmt->bind_param("i", $kost_id);
$stmt->execute();
$kost = $stmt->get_result()->fetch_assoc();
if (!$kost) {
    header("Location: ../index.php");
    exit;
}
$list_gambar = json_decode($kost['list_gambar'], true);

// Rekomendasi kost lain (3 random, exclude current)
$rec_stmt = $conn->prepare("SELECT id, nama_kost, lokasi, harga_per_bulan, list_gambar FROM kost WHERE id != ? ORDER BY RAND() LIMIT 3");
$rec_stmt->bind_param("i", $kost_id);
$rec_stmt->execute();
$rekomendasi = $rec_stmt->get_result();

// 3. Hitung rating rata-rata & count review
$rev = $conn->prepare("
  SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt
  FROM reviews 
  WHERE kost_id = ?
");
$rev->bind_param("i", $kost_id);
$rev->execute();
$stats = $rev->get_result()->fetch_assoc();
$avg_rating  = round($stats['avg_rating'],1);
$cnt_review  = $stats['cnt'];

// 4. Ambil semua review detail
$all_rev = $conn->prepare("
  SELECT r.rating, r.comment, r.created_at, u.username
  FROM reviews r
  JOIN users u ON r.user_id = u.id
  WHERE r.kost_id = ?
  ORDER BY r.created_at DESC
");
$all_rev->bind_param("i", $kost_id);
$all_rev->execute();
$reviews = $all_rev->get_result();

// 5. Handle submission review
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_review'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../views/login.php");
        exit;
    }
    $rating  = (int)$_POST['rating'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];
    $ins = $conn->prepare("
      INSERT INTO reviews (kost_id, user_id, rating, comment)
      VALUES (?, ?, ?, ?)
    ");
    $ins->bind_param("iiis", $kost_id, $user_id, $rating, $comment);
    $ins->execute();
    header("Location: detail_kost.php?id=$kost_id");
    exit;
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Detail <?=htmlspecialchars($kost['nama_kost'])?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Navbar + Search -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-success" href="../index.php">Rental Kost</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navContent">
      <div class="d-flex ms-auto align-items-center">
        <!-- Search & Filter seperti biasa -->
        <form class="d-flex me-2" action="../index.php" method="GET">
          <input type="text" name="q" class="form-control" placeholder="Cari Kost..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
          <button class="btn btn-outline-success ms-2" type="submit">
            <i class="bi bi-search"></i>
          </button>
        </form>
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
            <li><a class="dropdown-item" href="dashboard_user.php">Dashboard</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
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
<!-- Di Sidebar box harga & sewa -->
<?php if (isset($_SESSION['role']) && $_SESSION['role']==='user'): ?>
    <a href="sewa.php?id=<?=$kost_id?>" class="btn btn-success w-100 mt-3">Sewa Sekarang</a>
<?php else: ?>
    <button class="btn btn-secondary w-100 mt-3 d-none" disabled title="Hanya penyewa yang bisa menyewa">Sewa Sekarang</button>
<?php endif; ?>

<!-- Form review -->
<?php if (isset($_SESSION['role']) && $_SESSION['role']==='user'): ?>
    <!-- tampilkan form review seperti biasa -->
<?php else: ?>
    <div class="alert alert-info mt-4 d-none">
        Hanya penyewa yang dapat memberikan review.
    </div>
<?php endif; ?>



<div class="container mb-5">
  <div class="row gx-4">
    <!-- Kiri: Detail Kost -->
    <div class="col-lg-8">
      <!-- Carousel Gambar -->
       <a href="../index.php" class="btn btn-white mb-3">
          <i class="bi bi-arrow-left"></i> Kembali
        </a>
      <div id="kostCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
        <div class="carousel-inner">
          <?php foreach(array_slice($list_gambar,0,3) as $i=>$img): ?>
            <div class="carousel-item <?= $i===0?'active':'' ?>">
              <img src="../uploads/<?=htmlspecialchars($img)?>" class="d-block w-100 img-cover">
            </div>
          <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" data-bs-target="#kostCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" data-bs-target="#kostCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>

      <!-- Info Utama -->
      <h2><?=htmlspecialchars($kost['nama_kost'])?></h2>
      <p class="text-muted"><?=htmlspecialchars($kost['lokasi'])?></p>
      <div class="mb-3">
        <span class="badge bg-success"><i class="bi bi-star-fill"></i> <?=$avg_rating?> (<?=$cnt_review?>)</span>
      </div>
      <h5>Deskripsi</h5>
      <p><?=nl2br(htmlspecialchars($kost['deskripsi']))?></p>
      <h5>Fasilitas</h5>
      <p><?=htmlspecialchars($kost['fasilitas_kamar'])?></p>

      <!-- Form Review -->
      <?php if(isset($_SESSION['user_id'])): ?>
      <div class="mt-5">
        <h5>Tambah Review</h5>
        <form method="POST" action="">
          <div class="mb-3">
            <label class="form-label">Rating</label>
            <div id="starDisplay" class="d-flex align-items-center">
              <?php for($i=1;$i<=5;$i++): ?>
                <i class="bi bi-star" data-value="<?=$i?>" style="font-size:1.5em;cursor:pointer;color:#ffc107;margin-right:2px;"></i>
              <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="ratingInput" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Komentar</label>
            <textarea name="comment" class="form-control" rows="3" required></textarea>
          </div>
          <button name="add_review" class="btn btn-primary">Kirim Review</button>
        </form>
      </div>
      <?php else: ?>
      <div class="mt-5 alert alert-info">
        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a> untuk menambahkan review.
      </div>
      <?php endif; ?>

      <!-- List Review -->
      <div class="mt-4">
        <h5>Review Pengguna</h5>
        <?php if($reviews->num_rows>0): ?>
          <?php while($rv = $reviews->fetch_assoc()): ?>
            <div class="mb-3 p-3 bg-light rounded">
              <div>
                <strong><?=htmlspecialchars($rv['username'])?></strong>
                <span class="text-warning"><?=str_repeat('★',$rv['rating'])?></span>
              </div>
              <p><?=htmlspecialchars($rv['comment'])?></p>
              <small class="text-muted"><?=date('d M Y',strtotime($rv['created_at']))?></small>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>Belum ada review.</p>
        <?php endif; ?>
      </div>
      <!-- Rekomendasi Kost -->
      <div class="mt-5">
        <h5>Rekomendasi Kost Lainnya</h5>
        <div class="row">
          <?php while($r = $rekomendasi->fetch_assoc()): 
            $img = '';
            $imgs = json_decode($r['list_gambar'], true);
            if (is_array($imgs) && count($imgs) > 0) $img = $imgs[0];
          ?>
          <div class="col-md-4 mb-3">
            <div class="card h-100">
              <?php if($img): ?>
                <img src="../uploads/<?=htmlspecialchars($img)?>" class="card-img-top" style="height:120px;object-fit:cover;">
              <?php endif; ?>
              <div class="card-body p-2">
                <h6 class="card-title mb-1" style="font-size:1rem;"><?=htmlspecialchars($r['nama_kost'])?></h6>
                <div class="text-muted" style="font-size:0.9em;"><?=htmlspecialchars($r['lokasi'])?></div>
                <div class="fw-bold text-success mt-1" style="font-size:0.95em;">Rp<?=number_format($r['harga_per_bulan'],0,',','.')?></div>
                <a href="detail_kost.php?id=<?=$r['id']?>" class="btn btn-outline-primary btn-sm w-100 mt-2">Lihat Detail</a>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
      </div>
      <!-- End Rekomendasi Kost -->
    </div>
    

    <!-- Kanan: Sidebar Harga & Sewa -->
    <div class="col-lg-4">
      <div class="sticky-top" style="top:100px; z-index:100;">
        <div class="p-4 bg-white shadow-sm rounded border">
          <h5>Harga per Bulan</h5>
          <h3 class="text-primary">Rp<?=number_format($kost['harga_per_bulan'],0,',','.')?></h3>
          <p>Kamar Tersisa: <strong><?=htmlspecialchars($kost['kamar_tersisa'])?></strong></p>
          <?php if(isset($_SESSION['user_id'])): ?>
            <a href="#" class="btn btn-success w-100 mt-3">
              Sewa Sekarang
            </a>
          <?php else: ?>
            <button class="btn btn-success w-100 mt-3"
              onclick="alert('Silakan login terlebih dahulu untuk menyewa.')">
              Sewa Sekarang
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Login -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title">Masuk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="login.php" method="POST">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100">Masuk</button>
        </form>
        <div class="mt-3 text-center">
          <a href="forgot_password.php">Lupa Password?</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Star click sets rating
  document.querySelectorAll('#starDisplay .bi').forEach(function(star) {
    star.addEventListener('click', function() {
      var val = this.getAttribute('data-value');
      document.getElementById('ratingInput').value = val;
      updateStars(val);
    });
  });
  function updateStars(val) {
    document.querySelectorAll('#starDisplay .bi').forEach(function(star) {
      if (star.getAttribute('data-value') <= val) {
        star.classList.remove('bi-star');
        star.classList.add('bi-star-fill');
      } else {
        star.classList.remove('bi-star-fill');
        star.classList.add('bi-star');
      }
    });
  }
</script>
</body>
</html>
