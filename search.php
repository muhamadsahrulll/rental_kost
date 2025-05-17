<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: ../views/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Pencarian</title>
</head>
<body>
    <div class="container">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#">Dashboard</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/rental_kost/views/dashboard_user.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/rental_kost/search.php">Pencarian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="http://localhost/rental_kost/profile.php">Profile</a>
                    </li>
                </ul>
            </div>
        </nav>

        <h2>Pencarian</h2>
        <form>
            <div class="mb-3">
                <label for="search" class="form-label">Cari</label>
                <input type="text" class="form-control" id="search" placeholder="Masukkan kata kunci">
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>

    <script src="../assets/bootstrap.bundle.min.js"></script>
</body>
</html>
