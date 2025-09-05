<?php
require_once 'config/database.php';

// Get all packages
$packages = $conn->query("SELECT * FROM packages ORDER BY harga ASC");

// Get selected package from URL if exists
$selected_package_id = isset($_GET['package']) ? (int)$_GET['package'] : null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];
    $paket_id = $_POST['paket_id'];
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];
    $catatan = $_POST['catatan'];

    // Validate date (not in the past)
    $today = date('Y-m-d');
    if ($tanggal < $today) {
        $error = "Tanggal tidak boleh di masa lalu";
    } else {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (nama, email, no_hp, alamat, paket_id, tanggal, waktu, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssisss", $nama, $email, $no_hp, $alamat, $paket_id, $tanggal, $waktu, $catatan);
        
        if ($stmt->execute()) {
            $success = true;
            // Clear form data after successful submission
            $_POST = array();
        } else {
            $error = "Gagal membuat pesanan: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cuci Motor</title>
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

        /* Booking Form */
        .booking-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .form-label {
            font-weight: 500;
            color: var(--dark-bg);
        }

        .form-control {
            border: 2px solid #eee;
            padding: 0.8rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.1);
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

        /* Package Selection */
        .package-option {
            border: 2px solid #eee;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .package-option:hover {
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .package-option.selected {
            border-color: var(--secondary-color);
            background: rgba(231, 76, 60, 0.05);
        }

        .package-option h4 {
            color: var(--dark-bg);
            margin-bottom: 0.5rem;
        }

        .package-option .price {
            color: var(--secondary-color);
            font-weight: 600;
            font-size: 1.2rem;
        }

        .package-option .features {
            list-style: none;
            padding: 0;
            margin: 1rem 0 0;
        }

        .package-option .features li {
            padding: 0.3rem 0;
            color: #666;
            font-size: 0.9rem;
        }

        .package-option .features li i {
            color: var(--success-color);
            margin-right: 0.5rem;
        }

        /* Alert Styles */
        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        /* Footer */
        .footer {
            background: var(--dark-bg);
            color: white;
            padding: 3rem 0;
            margin-top: 4rem;
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
                        <a class="nav-link" href="index.php#orders">Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Kontak</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="booking-form">
                    <h2 class="text-center mb-4">Booking Cuci Motor</h2>

                    <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        Pesanan berhasil dibuat! Kami akan menghubungi Anda segera.
                    </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <!-- Package Selection -->
                        <div class="mb-4">
                            <label class="form-label">Pilih Paket</label>
                            <?php while($package = $packages->fetch_assoc()): ?>
                            <div class="package-option <?php echo ($selected_package_id == $package['id']) ? 'selected' : ''; ?>" 
                                 onclick="selectPackage(this, <?php echo $package['id']; ?>)">
                                <h4><?php echo htmlspecialchars($package['nama_paket']); ?></h4>
                                <div class="price">Rp <?php echo number_format($package['harga'], 0, ',', '.'); ?></div>
                                <ul class="features">
                                    <?php 
                                    $features = explode("\n", $package['deskripsi']);
                                    foreach($features as $feature): 
                                    ?>
                                    <li><i class="bi bi-check-circle"></i> <?php echo htmlspecialchars(trim($feature)); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endwhile; ?>
                            <input type="hidden" name="paket_id" id="paket_id" value="<?php echo $selected_package_id; ?>" required>
                        </div>

                        <!-- Personal Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. HP</label>
                                <input type="tel" class="form-control" name="no_hp" value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" min="<?php echo date('Y-m-d'); ?>" value="<?php echo isset($_POST['tanggal']) ? htmlspecialchars($_POST['tanggal']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu</label>
                                <input type="time" class="form-control" name="waktu" value="<?php echo isset($_POST['waktu']) ? htmlspecialchars($_POST['waktu']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alamat</label>
                                <input type="text" class="form-control" name="alamat" value="<?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" name="catatan" rows="3"><?php echo isset($_POST['catatan']) ? htmlspecialchars($_POST['catatan']) : ''; ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-calendar-check me-2"></i>
                            Buat Pesanan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
    <script>
        function selectPackage(element, packageId) {
            // Remove selected class from all packages
            document.querySelectorAll('.package-option').forEach(pkg => {
                pkg.classList.remove('selected');
            });
            
            // Add selected class to clicked package
            element.classList.add('selected');
            
            // Set the hidden input value
            document.getElementById('paket_id').value = packageId;
        }

        // Select package on page load if URL parameter exists
        document.addEventListener('DOMContentLoaded', function() {
            const selectedPackage = document.querySelector('.package-option.selected');
            if (selectedPackage) {
                const packageId = document.getElementById('paket_id').value;
                if (packageId) {
                    selectPackage(selectedPackage, packageId);
                }
            }
        });
    </script>
</body>
</html> 