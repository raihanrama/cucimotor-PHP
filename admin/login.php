<?php
session_start();
require_once '../config/database.php';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Cuci Motor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #f1c40f;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
            margin: 0;
        }

        .form-control {
            border: 2px solid #eee;
            padding: 0.8rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.1);
        }

        .btn-login {
            background: var(--secondary-color);
            color: white;
            padding: 0.8rem;
            border-radius: 10px;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #c0392b;
            color: white;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .back-link {
            color: white;
            text-decoration: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--accent-color);
            transform: translateX(-5px);
        }
    </style>
</head>
<body>
    <a href="../" class="back-link">
        <i class="bi bi-arrow-left"></i>
        Kembali ke Beranda
    </a>

    <div class="login-container">
        <div class="login-header">
            <h1>Login Admin</h1>
            <p>Masuk ke dashboard admin</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle me-2"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Masuk
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 