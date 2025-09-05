<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get berita by ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: berita.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM berita WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$berita = $result->fetch_assoc();

if (!$berita) {
    header("Location: berita.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = trim($_POST['judul']);
    $isi = trim($_POST['isi']);
    $status = $_POST['status'];
    
    // Validate input
    $errors = [];
    if (empty($judul)) {
        $errors[] = "Judul berita harus diisi!";
    }
    if (empty($isi)) {
        $errors[] = "Isi berita harus diisi!";
    }
    
    // Handle file upload
    $gambar = $berita['gambar']; // Keep existing image if no new upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['gambar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $errors[] = "Format file tidak didukung! Gunakan JPG, JPEG, PNG, atau GIF.";
        } else {
            $upload_dir = '../uploads/berita/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if ($berita['gambar'] && file_exists('../' . $berita['gambar'])) {
                    unlink('../' . $berita['gambar']);
                }
                $gambar = 'uploads/berita/' . $new_filename;
            } else {
                $errors[] = "Gagal mengupload file!";
            }
        }
    }
    
    // If no errors, update database
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE berita SET judul = ?, isi = ?, gambar = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $judul, $isi, $gambar, $status, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Berita berhasil diperbarui!";
            header("Location: berita.php");
            exit();
        } else {
            $errors[] = "Gagal memperbarui berita!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Berita - Admin Dashboard</title>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .sidebar {
            background: white;
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: #666;
            padding: 0.8rem 1rem;
            border-radius: 5px;
            margin: 0.2rem 0;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--secondary-color);
            color: white;
        }

        .main-content {
            padding: 2rem;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }

        .current-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <h5 class="mb-3">Menu Admin</h5>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                        <a class="nav-link" href="orders.php">
                            <i class="bi bi-list-check"></i> Pesanan
                        </a>
                        <a class="nav-link" href="packages.php">
                            <i class="bi bi-box"></i> Paket
                        </a>
                        <a class="nav-link active" href="berita.php">
                            <i class="bi bi-newspaper"></i> Berita
                        </a>
                        <a class="nav-link" href="reports.php">
                            <i class="bi bi-graph-up"></i> Laporan
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="bi bi-gear"></i> Pengaturan
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Edit Berita</h2>
                        <a href="berita.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="judul" class="form-label">Judul Berita *</label>
                                    <input type="text" class="form-control" id="judul" name="judul" 
                                           value="<?php echo htmlspecialchars($berita['judul']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="isi" class="form-label">Isi Berita *</label>
                                    <textarea class="form-control" id="isi" name="isi" rows="10" required><?php echo htmlspecialchars($berita['isi']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="gambar" class="form-label">Gambar (Opsional)</label>
                                    <?php if ($berita['gambar']): ?>
                                        <div class="mb-2">
                                            <p class="text-muted">Gambar saat ini:</p>
                                            <img src="../<?php echo htmlspecialchars($berita['gambar']); ?>" alt="Current Image" class="current-image">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                                    <small class="text-muted">Format yang didukung: JPG, JPEG, PNG, GIF. Maksimal 2MB. Biarkan kosong untuk mempertahankan gambar saat ini.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="published" <?php echo $berita['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                        <option value="draft" <?php echo $berita['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    </select>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Update Berita
                                    </button>
                                    <a href="berita.php" class="btn btn-secondary">
                                        <i class="bi bi-x"></i> Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 