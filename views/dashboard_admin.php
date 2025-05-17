<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../views/login.php");
    exit;
}

include '../includes/db.php';

// Statistik data
$stmt = $conn->prepare("SELECT COUNT(*) AS total_users FROM users");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total_users'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total_kosts FROM kost");
$stmt->execute();
$total_kosts = $stmt->get_result()->fetch_assoc()['total_kosts'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total_reviews FROM reviews");
$stmt->execute();
$total_reviews = $stmt->get_result()->fetch_assoc()['total_reviews'];

// Fetch all users
$stmt = $conn->prepare("SELECT id, username, email, role FROM users");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch all kost
$stmt = $conn->prepare("SELECT k.id, k.nama_kost, k.harga_per_bulan, k.kamar_tersisa, k.deskripsi, k.lokasi, k.fasilitas_kamar, k.is_verified, u.username AS pemilik 
                        FROM kost k 
                        JOIN users u ON k.pemilik_id = u.id");
$stmt->execute();
$kosts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch all reviews
$stmt = $conn->prepare("SELECT reviews.*, users.username, kost.nama_kost FROM reviews JOIN users ON reviews.user_id = users.id JOIN kost ON reviews.kost_id = kost.id ORDER BY reviews.created_at DESC");
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Verifikasi kost
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_kost'])) {
    // Ambil ID kost dari input form
    $kost_id = $_POST['kost_id'];

    // Query untuk memperbarui status verifikasi
    $stmt = $conn->prepare("UPDATE kost SET is_verified = 1 WHERE id = ?");
    $stmt->bind_param("i", $kost_id);

    if ($stmt->execute()) {
        // Redirect dengan pesan sukses
        $_SESSION['message'] = "Kost berhasil diverifikasi.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        // Redirect dengan pesan error
        $_SESSION['message'] = "Gagal memverifikasi kost.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

//delete review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    // Ambil ID review dari input form
    $review_id = $_POST['review_id'];

    // Query untuk menghapus review
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $review_id);

    if ($stmt->execute()) {
        // Redirect dengan pesan sukses
        $_SESSION['message'] = "Review berhasil dihapus.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        // Redirect dengan pesan error
        $_SESSION['message'] = "Gagal menghapus review.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            padding: 15px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .stat-card {
            border-radius: 12px;
            color: white;
            text-align: center;
            padding: 20px;
            margin: 10px 0;
        }
        .stat-card h1 {
            font-size: 2.5em;
        }
        .stat-card.users { background-color: #0d6efd; }
        .stat-card.kosts { background-color: #198754; }
        .stat-card.reviews { background-color: #dc3545; }
        .content-section {
            display: none;
        }
        .content-section.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="#dashboard" onclick="showSection('dashboard')">Dashboard</a>
        <a href="#users" onclick="showSection('users')">Daftar User</a>
        <a href="#kosts" onclick="showSection('kosts')">Daftar Kost</a>
        <a href="#reviews" onclick="showSection('reviews')">Daftar Review</a>
        <a href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard -->
        <div id="dashboard" class="content-section active">
            <h1 class="mb-4 text-center">Dashboard Admin</h1>
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card users">
                        <h1><?= $total_users; ?></h1>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card kosts">
                        <h1><?= $total_kosts; ?></h1>
                        <p>Total Kost</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card reviews">
                        <h1><?= $total_reviews; ?></h1>
                        <p>Total Reviews</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar User -->
        <div id="users" class="content-section">
            <h2>Daftar User</h2>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id']; ?></td>
                                <td><?= htmlspecialchars($user['username']); ?></td>
                                <td><?= htmlspecialchars($user['email']); ?></td>
                                <td><?= htmlspecialchars($user['role']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daftar Kost -->
        <div id="kosts" class="content-section">
            <h2>Daftar Kost</h2>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama Kost</th>
                            <th>Harga</th>
                            <th>Kamar Tersisa</th>
                            <th>Pemilik</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kosts as $kost): ?>
                            <tr>
                                <td><?= $kost['id']; ?></td>
                                <td><?= htmlspecialchars($kost['nama_kost']); ?></td>
                                <td>Rp <?= number_format($kost['harga_per_bulan'], 0, ',', '.'); ?></td>
                                <td><?= $kost['kamar_tersisa']; ?></td>
                                <td><?= htmlspecialchars($kost['pemilik']); ?></td>
                                <td>
                                    <?= $kost['is_verified'] ? "Terverifikasi" : "Belum Terverifikasi"; ?>
                                    <?php if (!$kost['is_verified']): ?>
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="kost_id" value="<?= $kost['id']; ?>">
                                            <button type="submit" name="verify_kost" class="btn btn-success btn-sm">Verifikasi</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        </div>

        <!-- Daftar Review -->
        <div id="reviews" class="content-section">
            <h2>Daftar Review</h2>
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="star-rating">
                                <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                    ★
                                <?php endfor; ?>
                            </div>
                            <p><strong><?= htmlspecialchars($review['username']); ?></strong> - <?= htmlspecialchars($review['nama_kost']); ?></p>
                            <p><?= htmlspecialchars($review['comment']); ?></p>
                            <small><?= date('d M Y H:i', strtotime($review['created_at'])); ?></small>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="review_id" value="<?= $review['id']; ?>">
                                <button type="submit" name="delete_review" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Belum ada review.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => section.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active');
        }
    </script>
</body>
</html>


