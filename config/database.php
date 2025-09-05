<?php
$host = 'localhost';
$dbname = 'cuci_motor';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Create tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS packages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_paket VARCHAR(100) NOT NULL,
        harga DECIMAL(10,2) NOT NULL,
        deskripsi TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        no_hp VARCHAR(20) NOT NULL,
        alamat TEXT NOT NULL,
        paket_id INT NOT NULL,
        tanggal DATE NOT NULL,
        waktu TIME NOT NULL,
        catatan TEXT,
        status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (paket_id) REFERENCES packages(id)
    )",

    "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nama_lengkap VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        no_hp VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )"
];

// Execute each table creation query
foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        die("Error creating table: " . $conn->error);
    }
}

// Insert default packages if table is empty
$check_packages = $conn->query("SELECT COUNT(*) as count FROM packages");
$row = $check_packages->fetch_assoc();

if ($row['count'] == 0) {
    $default_packages = [
        [
            'nama_paket' => 'Paket Basic',
            'harga' => 25000,
            'deskripsi' => "Cuci motor standar\nPembersihan body motor\nPembersihan jok\nPembersihan roda"
        ],
        [
            'nama_paket' => 'Paket Premium',
            'harga' => 35000,
            'deskripsi' => "Cuci motor standar\nPembersihan body motor\nPembersihan jok\nPembersihan roda\nPembersihan mesin\nPembersihan rantai\nSemir ban"
        ],
        [
            'nama_paket' => 'Paket VIP',
            'harga' => 50000,
            'deskripsi' => "Cuci motor standar\nPembersihan body motor\nPembersihan jok\nPembersihan roda\nPembersihan mesin\nPembersihan rantai\nSemir ban\nPoles body\nPembersihan karburator\nPembersihan filter udara"
        ]
    ];

    $stmt = $conn->prepare("INSERT INTO packages (nama_paket, harga, deskripsi) VALUES (?, ?, ?)");
    
    foreach ($default_packages as $package) {
        $stmt->bind_param("sds", $package['nama_paket'], $package['harga'], $package['deskripsi']);
        $stmt->execute();
    }
    
    $stmt->close();
}

// Insert default admin if not exists
$check_admin = $conn->query("SELECT COUNT(*) as count FROM admins");
$row = $check_admin->fetch_assoc();

if ($row['count'] == 0) {
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO admins (username, password, nama_lengkap, email) VALUES (?, ?, ?, ?)");
    $username = 'admin';
    $nama_lengkap = 'Administrator';
    $email = 'admin@example.com';
    $stmt->bind_param("ssss", $username, $default_password, $nama_lengkap, $email);
    $stmt->execute();
    $stmt->close();
}
?> 