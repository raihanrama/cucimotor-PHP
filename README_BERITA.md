# Fitur Berita - Cuci Motor JWP

## Deskripsi
Fitur berita memungkinkan admin untuk menambahkan, mengedit, dan mengelola berita yang akan ditampilkan di halaman user. Berita dapat ditampilkan di halaman home dan halaman about.

## Struktur Database

### Tabel `berita`
```sql
CREATE TABLE `berita` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('published','draft') DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## File yang Dibuat/Dimodifikasi

### 1. Database
- `cuci_motor.sql` - Menambahkan tabel berita

### 2. Admin Panel
- `admin/berita.php` - Halaman utama untuk mengelola berita
- `admin/add_berita.php` - Halaman untuk menambah berita baru
- `admin/edit_berita.php` - Halaman untuk mengedit berita

### 3. User Interface
- `about.php` - Halaman about yang menampilkan berita
- `index.php` - Menambahkan section berita di halaman home

### 4. Keamanan
- `uploads/.htaccess` - Mengamankan direktori uploads

## Fitur Admin

### 1. Kelola Berita (`admin/berita.php`)
- Melihat daftar semua berita
- Menambah berita baru
- Mengedit berita yang ada
- Menghapus berita
- Filter berdasarkan status (published/draft)

### 2. Tambah Berita (`admin/add_berita.php`)
- Form untuk menambah berita baru
- Upload gambar (opsional)
- Validasi input
- Status berita (published/draft)

### 3. Edit Berita (`admin/edit_berita.php`)
- Form untuk mengedit berita
- Preview gambar yang ada
- Upload gambar baru (opsional)
- Update status berita

## Fitur User

### 1. Halaman Home (`index.php`)
- Menampilkan 3 berita terbaru
- Preview gambar berita
- Link ke halaman about untuk berita lengkap

### 2. Halaman About (`about.php`)
- Menampilkan semua berita yang published
- Informasi tentang perusahaan
- Layout yang menarik dengan card berita

## Struktur Direktori Uploads
```
uploads/
└── berita/
    └── [file gambar berita]
```

## Keamanan

### 1. Validasi Input
- Judul berita wajib diisi
- Isi berita wajib diisi
- Validasi format gambar (JPG, JPEG, PNG, GIF)

### 2. Keamanan File Upload
- Direktori uploads dilindungi dengan .htaccess
- Hanya file gambar yang diizinkan
- Mencegah eksekusi file PHP

### 3. Sanitasi Output
- Menggunakan `htmlspecialchars()` untuk mencegah XSS
- Validasi ID berita sebelum edit/delete

## Cara Penggunaan

### Untuk Admin:
1. Login ke admin panel
2. Klik menu "Berita" di sidebar
3. Klik "Tambah Berita" untuk menambah berita baru
4. Isi form dengan judul, isi, dan upload gambar (opsional)
5. Pilih status (published/draft)
6. Klik "Simpan Berita"

### Untuk User:
1. Buka halaman home untuk melihat berita terbaru
2. Klik "About" di navbar untuk melihat semua berita
3. Berita akan ditampilkan dengan gambar dan tanggal publikasi

## Navigasi yang Ditambahkan

### Admin Panel:
- Menu "Berita" di sidebar semua halaman admin

### User Interface:
- Link "About" di navbar halaman home
- Section berita di halaman home
- Halaman about yang menampilkan berita

## Catatan Penting

1. **Direktori Uploads**: Pastikan direktori `uploads/berita/` memiliki permission yang tepat (777 untuk development, 755 untuk production)

2. **Gambar Berita**: Gambar akan disimpan dengan nama unik untuk menghindari konflik

3. **Status Berita**: 
   - `published`: Berita akan ditampilkan di halaman user
   - `draft`: Berita hanya terlihat di admin panel

4. **Responsive Design**: Semua halaman berita responsive untuk mobile dan desktop

5. **SEO Friendly**: Struktur HTML yang baik untuk SEO

## Troubleshooting

### Jika gambar tidak muncul:
1. Periksa permission direktori uploads
2. Pastikan path gambar benar
3. Periksa file .htaccess di direktori uploads

### Jika berita tidak muncul di halaman user:
1. Pastikan status berita adalah 'published'
2. Periksa query database
3. Pastikan tidak ada error PHP

### Jika admin tidak bisa login:
1. Periksa session admin
2. Pastikan file login.php berfungsi dengan baik
3. Periksa koneksi database 