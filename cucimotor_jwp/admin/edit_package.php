<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_id = $_POST['package_id'];
    $nama_paket = $_POST['nama_paket'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];

    // Validate input
    if (empty($package_id) || empty($nama_paket) || empty($harga) || empty($deskripsi)) {
        $_SESSION['error'] = "Semua field harus diisi!";
        header('Location: packages.php');
        exit;
    }

    // Update package
    $stmt = $conn->prepare("UPDATE packages SET nama_paket = ?, harga = ?, deskripsi = ? WHERE id = ?");
    $stmt->bind_param("sisi", $nama_paket, $harga, $deskripsi, $package_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Paket berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui paket: " . $conn->error;
    }

    $stmt->close();
    header('Location: packages.php');
    exit;
} else {
    header('Location: packages.php');
    exit;
} 