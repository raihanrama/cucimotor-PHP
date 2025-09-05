<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get all packages
$packages = $conn->query("SELECT * FROM packages ORDER BY harga ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Paket - Admin Dashboard</title>
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

        /* Sidebar Styles */
        .sidebar {
            background: var(--dark-bg);
            min-height: 100vh;
            padding: 1.5rem;
            color: white;
            position: fixed;
            width: 250px;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1.5rem;
        }

        .sidebar-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        .nav-link i {
            margin-right: 0.8rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }

        /* Package Cards */
        .package-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .package-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .package-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-bg);
            margin: 0;
        }

        .package-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .package-features {
            list-style: none;
            padding: 0;
            margin: 0 0 1.5rem 0;
        }

        .package-features li {
            padding: 0.5rem 0;
            color: #666;
            display: flex;
            align-items: center;
        }

        .package-features li i {
            color: var(--success-color);
            margin-right: 0.5rem;
        }

        .package-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .btn-edit {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .btn-delete {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        /* Add Package Button */
        .add-package {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--secondary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
            transition: all 0.3s ease;
        }

        .add-package:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 1rem;
            }

            .sidebar-header h3, .nav-link span {
                display: none;
            }

            .nav-link {
                text-align: center;
                padding: 0.8rem;
            }

            .nav-link i {
                margin: 0;
                font-size: 1.2rem;
            }

            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="orders.php">
                    <i class="bi bi-cart"></i>
                    <span>Pesanan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="packages.php">
                    <i class="bi bi-box"></i>
                    <span>Paket</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="bi bi-graph-up"></i>
                    <span>Laporan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="settings.php">
                    <i class="bi bi-gear"></i>
                    <span>Pengaturan</span>
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Kelola Paket</h2>
                <div class="text-muted"><?php echo date('d F Y'); ?></div>
            </div>

            <!-- Packages Grid -->
            <div class="row">
                <?php while ($package = $packages->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="package-card">
                        <div class="package-header">
                            <h3 class="package-title"><?php echo htmlspecialchars($package['nama_paket']); ?></h3>
                            <div class="package-price">Rp <?php echo number_format($package['harga']); ?></div>
                        </div>
                        <ul class="package-features">
                            <?php 
                            $features = explode("\n", $package['deskripsi']);
                            foreach ($features as $feature): 
                            ?>
                            <li>
                                <i class="bi bi-check-circle"></i>
                                <?php echo htmlspecialchars(trim($feature)); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="package-actions">
                            <button type="button" class="btn btn-action btn-edit" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editPackageModal"
                                    data-package-id="<?php echo $package['id']; ?>"
                                    data-package-name="<?php echo htmlspecialchars($package['nama_paket']); ?>"
                                    data-package-price="<?php echo $package['harga']; ?>"
                                    data-package-description="<?php echo htmlspecialchars($package['deskripsi']); ?>">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <a href="delete_package.php?id=<?php echo $package['id']; ?>" 
                               class="btn btn-action btn-delete"
                               onclick="return confirm('Apakah Anda yakin ingin menghapus paket ini?')">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Add Package Button -->
            <a href="#" class="add-package" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                <i class="bi bi-plus"></i>
            </a>
        </div>
    </div>

    <!-- Add Package Modal -->
    <div class="modal fade" id="addPackageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Paket Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="add_package.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Paket</label>
                            <input type="text" class="form-control" name="nama_paket" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="harga" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="4" required></textarea>
                            <small class="text-muted">Gunakan baris baru untuk setiap fitur</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Package Modal -->
    <div class="modal fade" id="editPackageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Paket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="edit_package.php">
                    <div class="modal-body">
                        <input type="hidden" name="package_id" id="edit_package_id">
                        <div class="mb-3">
                            <label class="form-label">Nama Paket</label>
                            <input type="text" class="form-control" name="nama_paket" id="edit_nama_paket" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="harga" id="edit_harga" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="4" required></textarea>
                            <small class="text-muted">Gunakan baris baru untuk setiap fitur</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit package modal
        document.addEventListener('DOMContentLoaded', function() {
            const editPackageModal = document.getElementById('editPackageModal');
            if (editPackageModal) {
                editPackageModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const packageId = button.getAttribute('data-package-id');
                    const packageName = button.getAttribute('data-package-name');
                    const packagePrice = button.getAttribute('data-package-price');
                    const packageDescription = button.getAttribute('data-package-description');
                    
                    const modal = this;
                    modal.querySelector('#edit_package_id').value = packageId;
                    modal.querySelector('#edit_nama_paket').value = packageName;
                    modal.querySelector('#edit_harga').value = packagePrice;
                    modal.querySelector('#edit_deskripsi').value = packageDescription;
                });
            }
        });
    </script>
</body>
</html> 