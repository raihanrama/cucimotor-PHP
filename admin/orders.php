<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get all orders with package details
$orders = $conn->query("
    SELECT o.*, p.nama_paket, p.harga as total_amount 
    FROM orders o 
    JOIN packages p ON o.paket_id = p.id 
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin Dashboard</title>
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

        /* Table Container */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            color: var(--dark-bg);
            border-bottom: 2px solid #eee;
        }

        .table td {
            vertical-align: middle;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        .status-completed {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }

        .status-cancelled {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        /* Action Buttons */
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

        .btn-whatsapp {
            background: rgba(37, 211, 102, 0.1);
            color: #25d366;
        }

        /* Search and Filter */
        .search-filter {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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
                <a class="nav-link active" href="orders.php">
                    <i class="bi bi-cart"></i>
                    <span>Pesanan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="packages.php">
                    <i class="bi bi-box"></i>
                    <span>Paket</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="berita.php">
                    <i class="bi bi-newspaper"></i>
                    <span>Berita</span>
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
                <h2 class="mb-0">Kelola Pesanan</h2>
                <div class="text-muted"><?php echo date('d F Y'); ?></div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="Cari pesanan...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>No. Telepon</th>
                                <th>Alamat</th>
                                <th>Paket</th>
                                <th>Total</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['nama']); ?></td>
                                <td>
                                    <span class="d-block mb-1"><?php echo htmlspecialchars($order['no_hp']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($order['alamat']); ?></td>
                                <td><?php echo htmlspecialchars($order['nama_paket']); ?></td>
                                <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-success" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'completed')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'pending')">
                                            <i class="bi bi-clock"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'cancelled')">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        <?php if (strtolower($order['status']) === 'cancelled'): ?>
                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $order['no_hp']); ?>" class="btn btn-whatsapp btn-action" target="_blank" title="Hubungi via WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="update_status.php">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="order_id">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="status" required>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
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
        function updateOrderStatus(orderId, status) {
            if (!confirm('Apakah Anda yakin ingin mengubah status pesanan ini?')) {
                return;
            }

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('status', status);

            fetch('update_order_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Status berhasil diperbarui');
                    location.reload();
                } else {
                    alert('Gagal memperbarui status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memperbarui status');
            });
        }
    </script>
</body>
</html> 