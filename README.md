🏪 Mini Project Toko — Aplikasi Manajemen Toko Berbasis Web

Mini Project Toko adalah aplikasi berbasis web yang dibuat menggunakan PHP, MySQL, dan Bootstrap.
Aplikasi ini dirancang untuk membantu dalam mengelola data barang, pembeli, dan transaksi penjualan dengan tampilan dashboard yang interaktif dan mudah digunakan.

✨ Fitur Utama

📦 Manajemen Barang
Tambah, ubah, hapus, dan lihat data barang lengkap dengan harga serta stok.

🧑‍🤝‍🧑 Manajemen Pembeli
Menyimpan data pembeli seperti nama dan alamat untuk keperluan transaksi.

💰 Transaksi Penjualan
Mencatat setiap transaksi yang terjadi, menghitung total harga otomatis, dan menampilkan daftar riwayat transaksi.

📊 Dashboard Penjualan
Menampilkan ringkasan data toko secara real-time:

Total Barang

Total Pembeli

Total Transaksi

Total Pendapatan

Barang Terlaris

📋 Tabel Data Responsif
Menampilkan seluruh data dengan tampilan tabel Bootstrap yang rapi dan mudah dibaca.

🛠️ Teknologi yang Digunakan
Komponen	Teknologi
💻 Frontend	HTML, CSS, Bootstrap 5
⚙️ Backend	PHP (Procedural)
🗄️ Database	MySQL
🌐 Server Lokal	XAMPP / Apache
🧾 Version Control	Git & GitHub
⚙️ Panduan Instalasi

Clone repository ini

git clone https://github.com/miracle061/Mini-Project-Toko_Miracle-I-M-Tanate_22050974061.git


Pindahkan folder ke direktori htdocs (XAMPP).

C:\xampp\htdocs\

Import database

Buka phpMyAdmin

Buat database baru, misalnya: toko_db

Import file toko.sql dari folder database/

Konfigurasi koneksi database
Edit file src/config.php dan sesuaikan:

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "toko1";

Jalankan server Apache dan MySQL, lalu buka di browser:

http://localhost/toko1/public/index.php

📈 Tampilan Dashboard

Dashboard menampilkan ringkasan kinerja toko Anda, seperti:

Jumlah barang yang tersedia

Total pembeli terdaftar

Total transaksi dan pendapatan

Barang yang paling sering terjual

📂 Struktur Proyek
## 📂 Struktur Proyek
```text
Mini-Project-Toko/
├── assets/                  # Folder untuk file frontend (CSS & JS)
│   ├── script.js            # Logika interaktif (JavaScript)
│   └── style.css            # Tampilan dan gaya (CSS)
│
├── public/                  # Folder utama untuk halaman publik
│   ├── index.php            # Dashboard utama
│   ├── kelola_barang.php    # CRUD data barang
│   ├── kelola_pembeli.php   # CRUD data pembeli
│   ├── kelola_transaksi.php # CRUD data transaksi
│   ├── tambah_barang.php    # Form tambah barang
│   ├── tambah_pembeli.php   # Form tambah pembeli
│   ├── tambah_transaksi.php # Form tambah transaksi
│   ├── laporan.php          # Laporan penjualan / rekap transaksi
│   └── testdb.php           # File uji koneksi database
│
├── src/                     # Folder untuk logika backend / konfigurasi
│   └── config.php           # Konfigurasi koneksi database (MySQL)
│
├── template/                # Komponen layout halaman
│   ├── header.php           # Bagian atas halaman (HTML head, CSS)
│   ├── navbar.php           # Navigasi utama
│   └── footer.php           # Bagian bawah halaman (footer)
│
├── vendor/                  # Folder dependensi Composer (otomatis)
│
├── composer.json            # File konfigurasi Composer
├── composer.lock            # File versi paket Composer
└── TODO.md                  # Catatan tugas pengembangan (opsional)


👨‍💻 Pengembang

Miracle I. M. Tanate
🎓 22050974061 — Mini Project Mata Kuliah Pemrograman Web

📅 Tahun: 2025
🏫 Universitas Negeri Surabaya

📜 Lisensi

Proyek ini dibuat untuk tujuan pembelajaran dan tugas akademik.
