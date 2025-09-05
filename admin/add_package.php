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
    $nama_paket = $_POST['nama_paket'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];

    // Validate input
    if (empty($nama_paket) || empty($harga) || empty($deskripsi)) {
        $_SESSION['error'] = "Semua field harus diisi!";
        header('Location: packages.php');
        exit;
    }

    // Insert new package
    $stmt = $conn->prepare("INSERT INTO packages (nama_paket, harga, deskripsi) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $nama_paket, $harga, $deskripsi);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Paket berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan paket: " . $conn->error;
    }

    $stmt->close();
    header('Location: packages.php');
    exit;
} else {
    header('Location: packages.php');
    exit;
} 