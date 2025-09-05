<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get admin data
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// If admin not found, redirect to login
if (!$admin) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = false;
    $error = '';

    if (isset($_POST['update_profile'])) {
        $nama_lengkap = $_POST['nama_lengkap'];
        $email = $_POST['email'];
        $no_hp = $_POST['no_hp'];

        $stmt = $conn->prepare("UPDATE admins SET nama_lengkap = ?, email = ?, no_hp = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nama_lengkap, $email, $no_hp, $admin_id);
        
        if ($stmt->execute()) {
            $success = true;
            $admin['nama_lengkap'] = $nama_lengkap;
            $admin['email'] = $email;
            $admin['no_hp'] = $no_hp;
        } else {
            $error = "Gagal memperbarui profil: " . $conn->error;
        }
    } elseif (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!password_verify($current_password, $admin['password'])) {
            $error = "Password saat ini tidak sesuai";
        } elseif ($new_password !== $confirm_password) {
            $error = "Password baru dan konfirmasi password tidak sesuai";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $admin_id);
            
            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = "Gagal memperbarui password: " . $conn->error;
            }
        }
    } elseif (isset($_POST['create_admin'])) {
        $new_username = trim($_POST['new_username']);
        $new_nama_lengkap = trim($_POST['new_nama_lengkap']);
        $new_email = trim($_POST['new_email']);
        $new_no_hp = trim($_POST['new_no_hp']);
        $new_password = $_POST['new_password'];
        $new_confirm_password = $_POST['new_confirm_password'];

        // Validasi
        if (empty($new_username) || empty($new_nama_lengkap) || empty($new_email) || empty($new_password) || empty($new_confirm_password)) {
            $error = 'Semua field harus diisi untuk membuat akun admin baru!';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password minimal 6 karakter!';
        } elseif ($new_password !== $new_confirm_password) {
            $error = 'Password dan konfirmasi password tidak sama!';
        } else {
            // Cek username/email unik
            $stmt = $conn->prepare('SELECT id FROM admins WHERE username = ? OR email = ?');
            $stmt->bind_param('ss', $new_username, $new_email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = 'Username atau email sudah digunakan!';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('INSERT INTO admins (username, password, nama_lengkap, email, no_hp) VALUES (?, ?, ?, ?, ?)');
                $stmt->bind_param('sssss', $new_username, $hashed_password, $new_nama_lengkap, $new_email, $new_no_hp);
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $error = 'Gagal membuat akun admin baru: ' . $conn->error;
                }
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_admin'])) {
        $delete_id = intval($_POST['delete_id']);
        if ($delete_id === $admin_id) {
            $error = 'Anda tidak bisa menghapus akun Anda sendiri!';
        } else {
            $stmt = $conn->prepare('DELETE FROM admins WHERE id = ?');
            $stmt->bind_param('i', $delete_id);
            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = 'Gagal menghapus akun admin: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Ambil semua admin
$all_admins = $conn->query('SELECT * FROM admins ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Admin Dashboard</title>
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

        /* Settings Card */
        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .settings-card h4 {
            color: var(--dark-bg);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
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

        .btn-save {
            background: var(--secondary-color);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            background: #c0392b;
            color: white;
            transform: translateY(-2px);
        }

        /* Alert Styles */
        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
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
                <a class="nav-link" href="reports.php">
                    <i class="bi bi-graph-up"></i>
                    <span>Laporan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="settings.php">
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
                <h2 class="mb-0">Pengaturan</h2>
                <div class="text-muted"><?php echo date('d F Y'); ?></div>
            </div>

            <?php if (isset($success) && $success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                Perubahan berhasil disimpan!
            </div>
            <?php endif; ?>

            <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- Profile Settings -->
            <div class="settings-card">
                <h4>Profil</h4>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" value="<?php echo htmlspecialchars($admin['nama_lengkap']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP</label>
                            <input type="tel" class="form-control" name="no_hp" value="<?php echo htmlspecialchars($admin['no_hp'] ?? ''); ?>">
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-save">
                        <i class="bi bi-save me-2"></i>
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            <!-- Password Settings -->
            <div class="settings-card">
                <h4>Ubah Password</h4>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-save">
                        <i class="bi bi-key me-2"></i>
                        Ubah Password
                    </button>
                </form>
            </div>

            <!-- Form Buat Akun Admin Baru -->
            <div class="settings-card mt-4">
                <h4>Buat Akun Admin Baru</h4>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="new_username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="new_nama_lengkap" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="new_email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP</label>
                            <input type="tel" class="form-control" name="new_no_hp">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" name="new_confirm_password" required>
                        </div>
                    </div>
                    <button type="submit" name="create_admin" class="btn btn-save">
                        <i class="bi bi-person-plus me-2"></i>
                        Buat Akun Admin
                    </button>
                </form>
            </div>

            <!-- Daftar Akun Admin -->
            <div class="settings-card mt-4">
                <h4>Daftar Akun Admin</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">No</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>No HP</th>
                                <th>Dibuat</th>
                                <th style="width:80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; while($row = $all_admins->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                                <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php if($row['id'] != $admin_id): ?>
                                    <form method="POST" onsubmit="return confirm('Yakin ingin menghapus akun admin ini?')">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_admin" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Hapus</button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-muted">(Anda)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 