<?php
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $hp = $_POST['hp'];
    $waktu = $_POST['waktu'];
    $status = 'Baru';
    $tanggal_pesan = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("INSERT INTO pesanan (nama, alamat, hp, waktu_pemesanan, status, tanggal_pesan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $alamat, $hp, $waktu, $status, $tanggal_pesan]);

        // Redirect back with success message
        header("Location: index.php?status=success");
        exit();
    } catch(PDOException $e) {
        // Redirect back with error message
        header("Location: index.php?status=error");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?> 