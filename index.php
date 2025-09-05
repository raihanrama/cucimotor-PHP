<?php
require_once 'config/database.php';

// Get all packages
$packages = $conn->query("SELECT * FROM packages ORDER BY harga ASC");

// Get recent orders
$recent_orders = $conn->query("
    SELECT o.*, p.nama_paket, p.harga as total_harga 
    FROM orders o 
    JOIN packages p ON o.paket_id = p.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");

// Get latest published berita
$latest_berita = $conn->query("SELECT * FROM berita WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuci Motor - Layanan Cuci Motor Terbaik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #f1c40f;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-bg: #2c3e50;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
        }

        /* Navbar Styles */
        .navbar {
            background: var(--dark-bg);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: white !important;
        }

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--secondary-color);
            border: none;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(44, 62, 80, 0.9)), url('assets/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            padding: 6rem 0;
            color: white;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        /* Package Cards */
        .package-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .package-card h3 {
            color: var(--dark-bg);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .package-card .price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
        }

        .package-card .features {
            list-style: none;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .package-card .features li {
            padding: 0.5rem 0;
            color: #666;
        }

        .package-card .features li i {
            color: var(--success-color);
            margin-right: 0.5rem;
        }

        /* Berita Cards */
        .berita-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .berita-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .berita-card h3 {
            color: var(--dark-bg);
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }

        .berita-card .date {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .berita-card .content {
            color: #555;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .berita-image {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        /* Recent Orders */
        .recent-orders {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 3rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .recent-orders h2 {
            color: var(--dark-bg);
            margin-bottom: 1.5rem;
        }

        .order-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item .status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status.completed {
            background: #d4edda;
            color: #155724;
        }

        .status.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        /* Footer */
        .footer {
            background: #232d3b;
            color: white;
            padding: 3rem 0;
            margin-top: 0;
        }

        .footer h4 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .footer p {
            opacity: 0.8;
        }

        .footer .social-links a {
            color: white;
            font-size: 1.5rem;
            margin-right: 1rem;
            transition: all 0.3s ease;
        }

        .footer .social-links a:hover {
            color: var(--secondary-color);
            transform: translateY(-3px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero {
                padding: 4rem 0;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .package-card {
                margin-bottom: 1.5rem;
            }
        }

        /* Tambahkan style untuk background section */
        .section-bg-light {
            background: var(--light-bg);
        }
        .section-bg-accent {
            background: #fffbe6;
        }
        .section-bg-dark {
            background: var(--dark-bg);
        }
        /* Tambahkan style agar judul dan card di section gelap tetap kontras */
        .section-bg-dark h2, .section-bg-dark .card-status, .section-bg-dark .package-card, .section-bg-dark .form-label, .section-bg-dark label, .section-bg-dark .text-center, .section-bg-dark .form-control {
            color: white !important;
        }
        .section-bg-dark .card-status, .section-bg-dark .package-card {
            background: #232d3b;
            color: white;
        }
        .section-bg-dark .form-control {
            background: #2c3e50;
            border-color: #444;
            color: white;
        }
        .section-bg-dark .form-control:focus {
            border-color: var(--secondary-color);
            background: #232d3b;
            color: white;
        }
        .section-bg-dark .input-group-text {
            background: #232d3b;
            color: #fff;
            border-color: #444;
        }
        .section-bg-dark .btn-primary {
            background: var(--secondary-color);
            color: white;
        }
        .section-bg-dark .btn-primary:hover {
            background: #c0392b;
            color: white;
        }
        .section-bg-dark .alert {
            color: #fff;
            background: #444;
            border: none;
        }
        .section-bg-dark .package-card, .section-bg-dark .package-card h3, .section-bg-dark .package-card .price, .section-bg-dark .package-card .features, .section-bg-dark .package-card .features li {
            color: white !important;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Cuci Motor</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#packages">Paket</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#cek-status">Cek Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Layanan Cuci Motor Terbaik</h1>
                    <p>Kami menyediakan layanan cuci motor profesional dengan berbagai paket yang dapat disesuaikan dengan kebutuhan Anda.</p>
                    <a href="booking.php" class="btn btn-primary">Booking Sekarang</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section id="packages" class="py-5 section-bg-dark">
        <div class="container">
            <h2 class="text-center mb-5">Paket Layanan</h2>
            <div class="row">
                <?php while($package = $packages->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="package-card">
                        <h3><?php echo htmlspecialchars($package['nama_paket']); ?></h3>
                        <div class="price">Rp <?php echo number_format($package['harga'], 0, ',', '.'); ?></div>
                        <ul class="features">
                            <?php 
                            $features = explode("\n", $package['deskripsi']);
                            foreach($features as $feature): 
                            ?>
                            <li><i class="bi bi-check-circle"></i> <?php echo htmlspecialchars(trim($feature)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="booking.php?package=<?php echo $package['id']; ?>" class="btn btn-primary w-100">Booking Sekarang</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Cek Status Section -->
    <section id="cek-status" class="py-5 section-bg-dark">
        <div class="container">
            <h2 class="text-center mb-5">Cek Status Pesanan</h2>
            <div class="status-container" style="max-width:600px;margin:0 auto;">
                <div class="card card-status">
                    <div class="card-header" style="background:var(--secondary-color);color:white;text-align:center;border-radius:15px 15px 0 0;padding:24px 20px 20px 20px;">
                        <h3 class="mb-0">Cek Status Pesanan</h3>
                    </div>
                    <div class="card-body" style="padding:2rem;">
                        <?php
                        $cek_error = null;
                        $cek_order = null;
                        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cek_status_hp'])) {
                            $cek_hp = trim($_POST['cek_status_hp']);
                            if (empty($cek_hp)) {
                                $cek_error = "Nomor HP harus diisi!";
                            } else {
                                $stmt = $conn->prepare("SELECT o.*, p.nama_paket, p.harga FROM orders o JOIN packages p ON o.paket_id = p.id WHERE o.no_hp = ? ORDER BY o.created_at DESC LIMIT 1");
                                $stmt->bind_param("s", $cek_hp);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $cek_order = $result->fetch_assoc();
                                if (!$cek_order) {
                                    $cek_error = "Pesanan tidak ditemukan!";
                                }
                            }
                        }
                        ?>
                        <?php if ($cek_error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $cek_error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($cek_order): ?>
                            <div class="alert alert-success">
                                <h5 class="alert-heading">Pesanan Ditemukan!</h5>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>ID Pesanan:</strong> #<?php echo $cek_order['id']; ?></p>
                                        <p class="mb-1"><strong>Nama:</strong> <?php echo htmlspecialchars($cek_order['nama']); ?></p>
                                        <p class="mb-1"><strong>Alamat:</strong> <?php echo htmlspecialchars($cek_order['alamat']); ?></p>
                                        <p class="mb-1"><strong>HP:</strong> <?php echo htmlspecialchars($cek_order['no_hp']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Paket:</strong> <?php echo htmlspecialchars($cek_order['nama_paket']); ?></p>
                                        <p class="mb-1"><strong>Harga:</strong> Rp <?php echo number_format($cek_order['harga'], 0, ',', '.'); ?></p>
                                        <p class="mb-1"><strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($cek_order['tanggal'])); ?></p>
                                        <p class="mb-1"><strong>Waktu:</strong> <?php echo date('H:i', strtotime($cek_order['waktu'])); ?></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <span class="badge <?php 
                                        echo $cek_order['status'] == 'pending' ? 'bg-warning' : 
                                            ($cek_order['status'] == 'completed' ? 'bg-success' : 'bg-danger'); 
                                    ?> status-badge">
                                        Status: <?php 
                                            if($cek_order['status']=='pending') echo 'Menunggu';
                                            elseif($cek_order['status']=='completed') echo 'Selesai';
                                            else echo 'Dibatalkan';
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="cek_status_hp" class="form-label">Nomor HP</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                    <input type="tel" class="form-control" id="cek_status_hp" name="cek_status_hp" value="<?php echo isset($_POST['cek_status_hp']) ? htmlspecialchars($_POST['cek_status_hp']) : ''; ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Cek Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Berita Section -->
    <section class="py-5 section-bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Berita & Informasi Terbaru</h2>
            <div class="row">
                <?php 
                $berita_count = 0;
                while($row = $latest_berita->fetch_assoc()): 
                    $berita_count++;
                ?>
                <div class="col-md-4">
                    <div class="berita-card">
                        <?php if ($row['gambar']): ?>
                            <img src="<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['judul']); ?>" class="berita-image">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($row['judul']); ?></h3>
                        <div class="date">
                            <i class="bi bi-calendar"></i> 
                            <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                        </div>
                        <div class="content">
                            <?php 
                            $content = htmlspecialchars($row['isi']);
                            if (strlen($content) > 150) {
                                echo substr($content, 0, 150) . '...';
                            } else {
                                echo $content;
                            }
                            ?>
                        </div>
                        <a href="about.php" class="btn btn-outline-primary">Baca Selengkapnya</a>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if ($berita_count == 0): ?>
                <div class="col-12">
                    <div class="text-center">
                        <i class="bi bi-newspaper" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3">Belum ada berita</h4>
                        <p class="text-muted">Berita akan ditampilkan di sini setelah admin menambahkan berita baru.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($berita_count > 0): ?>
            <div class="text-center mt-4">
                <a href="about.php" class="btn btn-primary">Lihat Semua Berita</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h4>Cuci Motor</h4>
                    <p>Layanan cuci motor profesional dengan kualitas terbaik untuk kendaraan Anda.</p>
                </div>
                <div class="col-md-4">
                    <h4>Kontak</h4>
                    <p>
                        <i class="bi bi-geo-alt"></i> Jl. Contoh No. 123<br>
                        <i class="bi bi-telephone"></i> (021) 1234-5678<br>
                        <i class="bi bi-envelope"></i> info@cucimotor.com
                    </p>
                </div>
                <div class="col-md-4">
                    <h4>Ikuti Kami</h4>
                    <div class="social-links">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 