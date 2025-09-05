<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get date range from request or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get total orders and income
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(p.harga) as total_income,
        SUM(CASE WHEN o.status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN o.status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
    FROM orders o
    JOIN packages p ON o.paket_id = p.id
    WHERE o.created_at BETWEEN ? AND ?
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

// Get orders by package
$stmt = $conn->prepare("
    SELECT 
        p.nama_paket,
        COUNT(*) as total_orders,
        SUM(p.harga) as total_income
    FROM orders o
    JOIN packages p ON o.paket_id = p.id
    WHERE o.created_at BETWEEN ? AND ?
    GROUP BY p.id, p.nama_paket
    ORDER BY total_orders DESC
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$package_stats = $stmt->get_result();

// Get daily orders for chart
$stmt = $conn->prepare("
    SELECT 
        DATE(o.created_at) as date,
        COUNT(*) as total_orders,
        SUM(p.harga) as total_income
    FROM orders o
    JOIN packages p ON o.paket_id = p.id
    WHERE o.created_at BETWEEN ? AND ?
    GROUP BY DATE(o.created_at)
    ORDER BY date
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$daily_stats = $stmt->get_result();

// Prepare data for chart
$dates = [];
$orders = [];
$income = [];
while ($row = $daily_stats->fetch_assoc()) {
    $dates[] = $row['date'];
    $orders[] = $row['total_orders'];
    $income[] = $row['total_income'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        }

        .stat-card h3 {
            color: var(--dark-bg);
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
        }

        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .table th {
            font-weight: 600;
            color: var(--dark-bg);
        }

        /* Date Filter */
        .date-filter {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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
                <a class="nav-link active" href="reports.php">
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
                <h2 class="mb-0">Laporan</h2>
                <div class="text-muted"><?php echo date('d F Y'); ?></div>
            </div>

            <!-- Date Filter -->
            <div class="date-filter">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <a href="export_excel.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-success w-100">
                            <i class="bi bi-file-excel me-2"></i>
                            Export Excel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3>Total Pesanan</h3>
                        <div class="value"><?php echo number_format($summary['total_orders']); ?></div>
                        <div class="label">Pesanan</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3>Total Pendapatan</h3>
                        <div class="value">Rp <?php echo number_format($summary['total_income']); ?></div>
                        <div class="label">Rupiah</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3>Pesanan Selesai</h3>
                        <div class="value"><?php echo number_format($summary['completed_orders']); ?></div>
                        <div class="label">Pesanan</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3>Pesanan Pending</h3>
                        <div class="value"><?php echo number_format($summary['pending_orders']); ?></div>
                        <div class="label">Pesanan</div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-md-8">
                    <div class="chart-container">
                        <h3>Grafik Pesanan Harian</h3>
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-container">
                        <h3>Pesanan per Paket</h3>
                        <canvas id="packagesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Package Statistics Table -->
            <div class="table-container">
                <h3>Statistik per Paket</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Paket</th>
                                <th>Total Pesanan</th>
                                <th>Total Pendapatan</th>
                                <th>Rata-rata per Pesanan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $package_stats->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nama_paket']); ?></td>
                                <td><?php echo number_format($row['total_orders']); ?></td>
                                <td>Rp <?php echo number_format($row['total_income']); ?></td>
                                <td>Rp <?php echo number_format($row['total_income'] / $row['total_orders']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Orders Chart
        const ordersCtx = document.getElementById('ordersChart').getContext('2d');
        new Chart(ordersCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Jumlah Pesanan',
                    data: <?php echo json_encode($orders); ?>,
                    borderColor: '#e74c3c',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Packages Chart
        const packagesCtx = document.getElementById('packagesChart').getContext('2d');
        new Chart(packagesCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($package_stats->fetch_all(MYSQLI_ASSOC), 'nama_paket')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($package_stats->fetch_all(MYSQLI_ASSOC), 'total_orders')); ?>,
                    backgroundColor: [
                        '#e74c3c',
                        '#3498db',
                        '#2ecc71',
                        '#f1c40f',
                        '#9b59b6'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html> 