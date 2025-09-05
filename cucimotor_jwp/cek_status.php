<?php
require_once 'config/database.php';
session_start();

$error = null;
$order = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hp = trim($_POST['hp']);
    
    if (empty($hp)) {
        $error = "Nomor HP harus diisi!";
    } else {
        try {
            // Fetch order with package information
            $stmt = $pdo->prepare("
                SELECT p.*, pk.nama_paket, pk.harga, pk.durasi 
                FROM pesanan p 
                JOIN paket pk ON p.paket_id = pk.id 
                WHERE p.hp = ? 
                ORDER BY p.tanggal_pesan DESC 
                LIMIT 1
            ");
            $stmt->execute([$hp]);
            $order = $stmt->fetch();
            
            if (!$order) {
                $error = "Pesanan tidak ditemukan!";
            }
        } catch(PDOException $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pesanan - CuciMotorku</title>
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
        .nav-link:hover, .nav-link.active {
            color: white !important;
            transform: translateY(-2px);
        }
        .status-container {
            max-width: 600px;
            margin: 60px auto 0 auto;
        }
        .card-status {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: none;
        }
        .card-header {
            background: var(--secondary-color);
            color: white;
            text-align: center;
            border-radius: 15px 15px 0 0 !important;
            padding: 24px 20px 20px 20px;
        }
        .card-body {
            padding: 2rem;
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
        .status-badge {
            font-size: 1.1em;
            padding: 8px 15px;
            border-radius: 20px;
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
                        <a class="nav-link active" href="cek_status.php">Cek Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="status-container">
            <div class="card card-status">
                <div class="card-header">
                    <h3 class="mb-0">Cek Status Pesanan</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($order): ?>
                        <div class="alert alert-success">
                            <h5 class="alert-heading">Pesanan Ditemukan!</h5>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>ID Pesanan:</strong> #<?php echo $order['id']; ?></p>
                                    <p class="mb-1"><strong>Nama:</strong> <?php echo htmlspecialchars($order['nama']); ?></p>
                                    <p class="mb-1"><strong>Alamat:</strong> <?php echo htmlspecialchars($order['alamat']); ?></p>
                                    <p class="mb-1"><strong>HP:</strong> <?php echo htmlspecialchars($order['hp']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Paket:</strong> <?php echo htmlspecialchars($order['nama_paket']); ?></p>
                                    <p class="mb-1"><strong>Harga:</strong> Rp <?php echo number_format($order['harga'], 0, ',', '.'); ?></p>
                                    <p class="mb-1"><strong>Durasi:</strong> <?php echo $order['durasi']; ?> menit</p>
                                    <p class="mb-1"><strong>Waktu Pemesanan:</strong><br><?php echo date('d/m/Y H:i', strtotime($order['waktu_pemesanan'])); ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <span class="badge <?php 
                                    echo $order['status'] == 'Baru' ? 'bg-primary' : 
                                        ($order['status'] == 'Diproses' ? 'bg-warning' : 'bg-success'); 
                                ?> status-badge">
                                    Status: <?php echo $order['status']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="hp" class="form-label">Nomor HP</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="tel" class="form-control" id="hp" name="hp" 
                                       value="<?php echo isset($_POST['hp']) ? htmlspecialchars($_POST['hp']) : ''; ?>"
                                       required>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html> 