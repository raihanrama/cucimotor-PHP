<?php
require_once 'config/database.php';

// Get published berita
$berita = $conn->query("SELECT * FROM berita WHERE status = 'published' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Cuci Motor</title>
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
            font-size: 1.5rem;
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
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        /* About Section */
        .about-section {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .about-section h2 {
            color: var(--dark-bg);
            margin-bottom: 1.5rem;
        }

        .about-section p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 1rem;
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

            .berita-card {
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
        .section-bg-dark h2, .section-bg-dark .card-status, .section-bg-dark .berita-card, .section-bg-dark .form-label, .section-bg-dark label, .section-bg-dark .text-center, .section-bg-dark .form-control {
            color: white !important;
        }
        .section-bg-dark .card-status, .section-bg-dark .berita-card {
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
        .section-bg-dark .berita-card, .section-bg-dark .berita-card h3, .section-bg-dark .berita-card .content {
            color: white !important;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Cuci Motor</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#packages">Paket</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#cek-status">Cek Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
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
                    <h1>Tentang Kami</h1>
                    <p>Pelajari lebih lanjut tentang layanan cuci motor profesional kami dan berita terbaru seputar bisnis kami.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-5">
        <div class="container">
            <div class="about-section">
                <h2>Tentang Cuci Motor</h2>
                <p>Kami adalah penyedia layanan cuci motor profesional yang berkomitmen untuk memberikan kualitas terbaik kepada pelanggan kami. Dengan pengalaman bertahun-tahun dalam industri ini, kami memahami betul kebutuhan dan harapan pelanggan terhadap layanan cuci motor yang berkualitas.</p>
                
                <p>Tim kami terdiri dari teknisi berpengalaman yang telah dilatih khusus untuk menangani berbagai jenis motor dengan perawatan yang tepat. Kami menggunakan produk-produk berkualitas tinggi dan peralatan modern untuk memastikan hasil yang memuaskan.</p>
                
                <p>Misi kami adalah memberikan layanan cuci motor yang tidak hanya bersih, tetapi juga memberikan perawatan yang tepat untuk menjaga kondisi motor Anda tetap prima. Kami percaya bahwa motor yang terawat dengan baik akan memberikan performa optimal dan umur yang lebih panjang.</p>
                
                <p>Terima kasih telah mempercayakan motor Anda kepada kami. Kami berkomitmen untuk terus memberikan layanan terbaik dan inovasi dalam industri cuci motor.</p>
            </div>
        </div>
    </section>

    <!-- Berita Section -->
    <section class="py-5 section-bg-dark">
        <div class="container">
            <h2 class="text-center mb-5">Berita & Informasi Terbaru</h2>
            <div class="row">
                <?php 
                $berita_count = 0;
                while($row = $berita->fetch_assoc()): 
                    $berita_count++;
                ?>
                <div class="col-md-6 col-lg-4">
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
                            if (strlen($content) > 200) {
                                echo substr($content, 0, 200) . '...';
                            } else {
                                echo $content;
                            }
                            ?>
                        </div>
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