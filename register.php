<?php
require_once 'config/database.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

$error = null;
$success = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    
    // Validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($nama_lengkap)) {
        $error = "Semua field harus diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "Username sudah digunakan!";
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, 'user')");
                $result = $stmt->execute([$username, $hashed_password, $nama_lengkap]);
                
                if ($result) {
                    $success = "Registrasi berhasil! Silahkan login.";
                } else {
                    $error = "Gagal melakukan registrasi. Silahkan coba lagi.";
                }
            }
        } catch(PDOException $e) {
            $error = "Terjadi kesalahan saat registrasi: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - CuciMotorku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            max-width: 500px;
            margin: 50px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">CuciMotorku</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="cek_status.php">Cek Status Pesanan</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="register-container">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Registrasi Akun</h3>
                </div>
                <div class="card-body p-4">
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

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                   value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>"
                                   required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">Register</button>
                        <p class="text-center mb-0">
                            Sudah punya akun? <a href="login.php">Login di sini</a>
                        </p>
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