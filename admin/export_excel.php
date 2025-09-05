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

// Get orders data
$stmt = $conn->prepare("
    SELECT 
        o.id,
        o.nama as nama_pelanggan,
        o.email,
        o.no_hp,
        o.alamat,
        p.nama_paket,
        p.harga,
        o.tanggal,
        o.waktu,
        o.status,
        o.created_at
    FROM orders o
    JOIN packages p ON o.paket_id = p.id
    WHERE o.created_at BETWEEN ? AND ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Laporan_Pesanan_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

// Output Excel content
echo "Laporan Pesanan Cuci Motor\n";
echo "Periode: " . date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date)) . "\n\n";

// Table headers
echo "ID\tNama Pelanggan\tEmail\tNo. HP\tAlamat\tPaket\tHarga\tTanggal\tWaktu\tStatus\tTanggal Pesanan\n";

// Table data
while ($row = $result->fetch_assoc()) {
    echo $row['id'] . "\t";
    echo $row['nama_pelanggan'] . "\t";
    echo $row['email'] . "\t";
    echo $row['no_hp'] . "\t";
    echo $row['alamat'] . "\t";
    echo $row['nama_paket'] . "\t";
    echo "Rp " . number_format($row['harga'], 0, ',', '.') . "\t";
    echo date('d/m/Y', strtotime($row['tanggal'])) . "\t";
    echo $row['waktu'] . "\t";
    echo ucfirst($row['status']) . "\t";
    echo date('d/m/Y H:i', strtotime($row['created_at'])) . "\n";
}

// Get summary data
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

// Output summary
echo "\n\nRingkasan\n";
echo "Total Pesanan: " . $summary['total_orders'] . "\n";
echo "Total Pendapatan: Rp " . number_format($summary['total_income'], 0, ',', '.') . "\n";
echo "Pesanan Selesai: " . $summary['completed_orders'] . "\n";
echo "Pesanan Pending: " . $summary['pending_orders'] . "\n";
echo "Pesanan Dibatalkan: " . $summary['cancelled_orders'] . "\n";

// Get package statistics
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

// Output package statistics
echo "\nStatistik per Paket\n";
echo "Paket\tTotal Pesanan\tTotal Pendapatan\tRata-rata per Pesanan\n";
while ($row = $package_stats->fetch_assoc()) {
    echo $row['nama_paket'] . "\t";
    echo $row['total_orders'] . "\t";
    echo "Rp " . number_format($row['total_income'], 0, ',', '.') . "\t";
    echo "Rp " . number_format($row['total_income'] / $row['total_orders'], 0, ',', '.') . "\n";
}
?> 