-- biar ga cape buat tabel manual 
-- jalanin di phpmyadmin sekali bae

CREATE DATABASE IF NOT EXISTS barbershop_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE barbershop_db;

-- tabel admin: nyimpen akun admin dashboard.
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- disimpen jadi hash bcrypt
    nama VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- tabel pelanggan: data orang yang mau booking
CREATE TABLE pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NUL,
    created_at DATABASE DEFAULT CURRENT_TIMESTAMP
);

-- tabel layanan: daftar jenis service apa bae yang ada
CREATE TABLE layanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    harga DECIMAL(10, 2) NOT NULL
);

-- tabel kouta: kapasitas orange booking per hari bae
CREATE TABLE kouta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL UNIQUE,
    kouta_harian INT NOT NULL DEFAULT 4,
    kouta_saat_ini INT NOT NULL DEFAULT 0
)

-- tabel pesanan: tabel utama sistem bookingnya
CREATE TABLE pemesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelanggan_id INT NOT NULL,
    layanan_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    status ENUM('menunggu', 'dikonfirmasi', 'selesai','dibatalkan')
    NOT NULL DEFAULT 'menunggu',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
    FOREIGN KEY (layanan_id) REFERENCES layanan(id)
)

-- tabel pembayaran: status bayar per pesenan
-- relasi 1-ke-1 dengan pesanan
CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pemesanan_id INT NOT NULL UNIQUE,
    metode ENUM('cash', 'transfer', 'qris') NOT NULL,
    status ENUM('menunggu', 'lunas', 'gagal') NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id)
)

-- tabel antrian: nomor urut booking per hari
CREATE TABLE antrian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pemesanan_id INT NOT NULL UNIQUE,
    nomor_antrian INT NOT NULL,
    tanggal DATE NOT NULL,
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id)
);

-- seed data: layaan yang tersedia di barber
INSERT INTO layaan (nama, harga_min, harga_max) VALUES
    ('Potong Rambut', 30000, 30000),
    ('Cukur Jenggot/Kumis', 10000, 10000),
    ('Cuci Rambut', 10000, 10000),
    ('Creambath', 40000, 40000),
    ('Warnain Rambut', 100000, 300000),
    ('Highlight Rambut', 100000, 300000),
    ('Booking Potong Rambut', 50000, 50000);

-- seed data: admin default
-- password di bawah itu tuh hash dari 'admin123'
INSERT INTO admin (username, password, nama) VALUES
    ('admin', 'masih kosong tar tak isi', 'Mas Abenk')