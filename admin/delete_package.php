<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "ID paket tidak ditemukan!";
    header('Location: packages.php');
    exit;
}

$package_id = $_GET['id'];

// Check if package exists
$check = $conn->prepare("SELECT id FROM packages WHERE id = ?");
$check->bind_param("i", $package_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Paket tidak ditemukan!";
    header('Location: packages.php');
    exit;
}

// Delete package
$stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
$stmt->bind_param("i", $package_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Paket berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus paket: " . $conn->error;
}

$stmt->close();
header('Location: packages.php');
exit; 