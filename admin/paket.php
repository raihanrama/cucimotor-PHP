<?php
require_once '../config/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$error = null;
$success = null;

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
                    $stmt = $pdo->prepare("INSERT INTO paket (nama_paket, deskripsi, harga, durasi) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        trim($_POST['nama_paket']),
                        trim($_POST['deskripsi']),
                        $_POST['harga'],
                        $_POST['durasi']
                    ]);
                    $success = "Paket berhasil ditambahkan!";
                    break;

                case 'edit':
                    $stmt = $pdo->prepare("UPDATE paket SET nama_paket = ?, deskripsi = ?, harga = ?, durasi = ? WHERE id = ?");
                    $stmt->execute([
                        trim($_POST['nama_paket']),
                        trim($_POST['deskripsi']),
                        $_POST['harga'],
                        $_POST['durasi'],
                        $_POST['id']
                    ]);
                    $success = "Paket berhasil diperbarui!";
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM paket WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $success = "Paket berhasil dihapus!";
                    break;
            }
        } catch(PDOException $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Fetch all packages
try {
    $stmt = $pdo->query("SELECT * FROM paket ORDER BY harga ASC");
    $paket_list = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Gagal mengambil data paket: " . $e->getMessage();
    $paket_list = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Paket - CuciMotorku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">CuciMotorku Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="paket.php">Paket</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Paket</h5>
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addPaketModal">
                    <i class="bi bi-plus-lg"></i> Tambah Paket
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Paket</th>
                                <th>Deskripsi</th>
                                <th>Harga</th>
                                <th>Durasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paket_list as $paket): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($paket['nama_paket']); ?></td>
                                <td><?php echo htmlspecialchars($paket['deskripsi']); ?></td>
                                <td>Rp <?php echo number_format($paket['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo $paket['durasi']; ?> menit</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editPaketModal"
                                            data-id="<?php echo $paket['id']; ?>"
                                            data-nama="<?php echo htmlspecialchars($paket['nama_paket']); ?>"
                                            data-deskripsi="<?php echo htmlspecialchars($paket['deskripsi']); ?>"
                                            data-harga="<?php echo $paket['harga']; ?>"
                                            data-durasi="<?php echo $paket['durasi']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deletePaketModal"
                                            data-id="<?php echo $paket['id']; ?>"
                                            data-nama="<?php echo htmlspecialchars($paket['nama_paket']); ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Package Modal -->
    <div class="modal fade" id="addPaketModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Paket Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="nama_paket" class="form-label">Nama Paket</label>
                            <input type="text" class="form-control" id="nama_paket" name="nama_paket" required>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="harga" class="form-label">Harga</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga" name="harga" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="durasi" class="form-label">Durasi (menit)</label>
                            <input type="number" class="form-control" id="durasi" name="durasi" required>
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
    <div class="modal fade" id="editPaketModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Paket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_nama_paket" class="form-label">Nama Paket</label>
                            <input type="text" class="form-control" id="edit_nama_paket" name="nama_paket" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_harga" class="form-label">Harga</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="edit_harga" name="harga" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_durasi" class="form-label">Durasi (menit)</label>
                            <input type="number" class="form-control" id="edit_durasi" name="durasi" required>
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

    <!-- Delete Package Modal -->
    <div class="modal fade" id="deletePaketModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Paket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <p>Apakah Anda yakin ingin menghapus paket <strong id="delete_nama"></strong>?</p>
                        <p class="text-danger">Perhatian: Tindakan ini tidak dapat dibatalkan!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit modal
        document.getElementById('editPaketModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');
            const deskripsi = button.getAttribute('data-deskripsi');
            const harga = button.getAttribute('data-harga');
            const durasi = button.getAttribute('data-durasi');

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama_paket').value = nama;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_harga').value = harga;
            document.getElementById('edit_durasi').value = durasi;
        });

        // Handle delete modal
        document.getElementById('deletePaketModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');

            document.getElementById('delete_id').value = id;
            document.getElementById('delete_nama').textContent = nama;
        });
    </script>
</body>
</html> 