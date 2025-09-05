<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get date range for income calculation
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // First day of current month
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); // Today

// Calculate income summary
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(p.harga) as total_income,
        COUNT(CASE WHEN o.status = 'completed' THEN 1 END) as completed_orders,
        COUNT(CASE WHEN o.status = 'pending' THEN 1 END) as pending_orders
    FROM orders o 
    JOIN packages p ON o.paket_id = p.id 
    WHERE o.tanggal BETWEEN ? AND ?
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

// Fetch orders with package information
$stmt = $conn->prepare("
    SELECT o.*, p.nama_paket, p.harga 
    FROM orders o 
    JOIN packages p ON o.paket_id = p.id 
    WHERE o.tanggal BETWEEN ? AND ?
    ORDER BY o.tanggal DESC
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cucimotor</title>
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

        /* Card Styles */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-card .title {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark-bg);
            margin-bottom: 0;
        }

        /* Table Styles */
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
                <a class="nav-link active" href="index.php">
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
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-4">Dashboard</h2>
                    
                    <!-- Date Filter -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-cart"></i>
                                </div>
                                <p class="title">Total Pesanan</p>
                                <h3 class="value"><?php echo number_format($summary['total_orders']); ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                                <p class="title">Total Pendapatan</p>
                                <h3 class="value">Rp <?php echo number_format($summary['total_income'], 0, ',', '.'); ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <p class="title">Pesanan Selesai</p>
                                <h3 class="value"><?php echo number_format($summary['completed_orders']); ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <p class="title">Pesanan Pending</p>
                                <h3 class="value"><?php echo number_format($summary['pending_orders']); ?></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders Table -->
                    <div class="table-container mt-4">
                        <h4 class="mb-4">Pesanan Terbaru</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Paket</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($order['nama_paket']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($order['tanggal'])); ?></td>
                                        <td>Rp <?php echo number_format($order['harga'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                                <?php 
                                                switch($order['status']) {
                                                    case 'pending':
                                                        echo 'Pending';
                                                        break;
                                                    case 'completed':
                                                        echo 'Selesai';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'Dibatalkan';
                                                        break;
                                                }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 